<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemEntryRequest;
use App\Jobs\PersistItemEntriesJob;
use App\Models\ItemEntry;
use App\Services\Contracts\ImageUploadServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ItemEntryController extends Controller
{
    public function __construct(private ImageUploadServiceInterface $imageUploadService)
    {
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $partyName = trim((string) $request->query('party_name', ''));
        $dateMode = in_array($request->query('date_mode'), ['latest', 'oldest', 'monthly'], true)
            ? (string) $request->query('date_mode')
            : 'latest';
        $month = (string) $request->query('month', '');
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';
        $sortableColumns = [
            'date' => 'created_at',
            'lart_number' => 'lart_number',
            'client_business_name' => 'client_business_name',
            'description' => 'description',
            'darjan' => 'darjan',
            'total_color' => 'total_color',
            'total_rate' => 'total_rate',
            'size_description' => 'size_description',
            'total_amount' => 'total_amount',
        ];
        $sort = array_key_exists($request->query('sort', ''), $sortableColumns)
            ? (string) $request->query('sort')
            : 'date';

        $query = ItemEntry::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('lart_number', 'like', "%{$search}%")
                        ->orWhere('client_business_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('size_description', 'like', "%{$search}%");
                });
            })
            ->when($partyName !== '', fn ($query) => $query->where('client_business_name', $partyName))
            ->when($dateMode === 'monthly' && preg_match('/^\d{4}-\d{2}$/', $month) === 1, function ($query) use ($month): void {
                [$year, $monthNumber] = explode('-', $month);

                $query->whereYear('created_at', (int) $year)
                    ->whereMonth('created_at', (int) $monthNumber);
            });

        $grandTotal = (clone $query)->sum('total_amount');

        if (! $request->has('sort') && in_array($dateMode, ['latest', 'oldest'], true)) {
            $direction = $dateMode === 'oldest' ? 'asc' : 'desc';
        }

        $itemEntries = $query
            ->orderBy($sortableColumns[$sort], $direction)
            ->orderBy('id', $direction)
            ->paginate(15)
            ->withQueryString();

        return view('item-entries.index', [
            'itemEntries' => $itemEntries,
            'partyNames' => ItemEntry::query()
                ->select('client_business_name')
                ->distinct()
                ->orderBy('client_business_name')
                ->pluck('client_business_name'),
            'filters' => [
                'search' => $search,
                'party_name' => $partyName,
                'date_mode' => $dateMode,
                'month' => $month,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'grandTotal' => $grandTotal,
        ]);
    }

    public function create(): View
    {
        return view('item-entries.create');
    }

    public function store(ItemEntryRequest $request): RedirectResponse
    {
        $preparedEntries = [];
        $uploadedPublicIds = [];

        try {
            foreach ($request->validatedEntries() as $index => $entry) {
                $upload = $this->imageUploadService->upload($request->file("entries.{$index}.image"));
                $uploadedPublicIds[] = $upload['public_id'];

                $preparedEntries[] = $this->payloadWithComputedAmount($entry, $upload['url']);
            }

            PersistItemEntriesJob::dispatch($preparedEntries);
        } catch (Throwable $exception) {
            $this->rollbackUploadedImages($uploadedPublicIds);

            return back()
                ->withInput()
                ->withErrors(['entries' => $exception->getMessage()]);
        }

        return to_route('item-entries.index')->with('status', 'Item entries are queued for persistence.');
    }

    public function edit(ItemEntry $itemEntry): View
    {
        return view('item-entries.edit', [
            'itemEntry' => $itemEntry,
        ]);
    }

    public function update(ItemEntryRequest $request, ItemEntry $itemEntry): RedirectResponse
    {
        $payload = $this->payloadWithComputedAmount($request->validatedEntry(), $itemEntry->image_url);
        $newPublicId = null;

        try {
            if ($request->hasFile('image')) {
                $upload = $this->imageUploadService->upload($request->file('image'));
                $newPublicId = $upload['public_id'];
                $payload['image_url'] = $upload['url'];
            }

            $oldPublicId = $this->publicIdFromUrl($itemEntry->image_url);
            $itemEntry->update($payload);

            if ($newPublicId !== null && $oldPublicId !== null) {
                $this->deleteImageQuietly($oldPublicId);
            }
        } catch (Throwable $exception) {
            if ($newPublicId !== null) {
                $this->deleteImageQuietly($newPublicId);
            }

            return back()
                ->withInput()
                ->withErrors(['image' => $exception->getMessage()]);
        }

        return to_route('item-entries.index')->with('status', 'Item entry updated.');
    }

    public function destroy(ItemEntry $itemEntry): RedirectResponse
    {
        $publicId = $this->publicIdFromUrl($itemEntry->image_url);

        if ($publicId !== null) {
            $this->deleteImageQuietly($publicId);
        }

        $itemEntry->delete();

        return to_route('item-entries.index')->with('status', 'Item entry deleted.');
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    private function payloadWithComputedAmount(array $entry, string $imageUrl): array
    {
        $darjan = (int) $entry['darjan'];
        $totalRate = (float) $entry['total_rate'];

        return array_merge(Arr::except($entry, ['image', 'total_amount']), [
            'image_url' => $imageUrl,
            'total_amount' => number_format($darjan * 12 * $totalRate, 2, '.', ''),
        ]);
    }

    /**
     * @param  array<int, string>  $publicIds
     */
    private function rollbackUploadedImages(array $publicIds): void
    {
        foreach ($publicIds as $publicId) {
            $this->deleteImageQuietly($publicId);
        }
    }

    private function deleteImageQuietly(string $publicId): void
    {
        try {
            $this->imageUploadService->delete($publicId);
        } catch (Throwable $exception) {
            Log::warning('Cloudinary image deletion failed.', [
                'public_id' => $publicId,
                'exception' => $exception,
            ]);
        }
    }

    private function publicIdFromUrl(string $imageUrl): ?string
    {
        $path = parse_url($imageUrl, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return null;
        }

        $path = preg_replace('#^/[^/]+/(?:image|video|raw)/upload/(?:v\d+/)?#', '', $path);

        if (! is_string($path) || $path === '') {
            return null;
        }

        return preg_replace('/\.[^.]+$/', '', $path) ?: null;
    }
}

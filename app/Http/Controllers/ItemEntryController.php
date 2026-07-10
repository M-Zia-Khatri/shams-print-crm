<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemEntryRequest;
use App\Jobs\PersistItemEntriesJob;
use App\Models\ItemEntry;
use App\Services\Contracts\ImageUploadServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ItemEntryController extends Controller
{
    public function __construct(private ImageUploadServiceInterface $imageUploadService)
    {
    }

    public function index(): View
    {
        return view('item-entries.index', [
            'itemEntries' => ItemEntry::query()->latest()->paginate(15),
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

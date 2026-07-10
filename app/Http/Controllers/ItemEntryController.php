<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemEntryRequest;
use App\Jobs\StoreItemEntryJob;
use App\Models\ItemEntry;
use App\Models\ItemEntryColor;
use App\Services\ItemEntryCalculationService;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;

class ItemEntryController extends Controller
{
    public function index(): View
    {
        return view('item-entries.index', [
            'itemEntries' => ItemEntry::query()
                ->with(['pieces.colors', 'pieces.sizes'])
                ->latest()
                ->get(),
            'colorTypes' => ItemEntryColor::query()
                ->distinct()
                ->orderBy('type')
                ->pluck('type'),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('item-entries.index', ['form' => 'create']);
    }

    public function store(ItemEntryRequest $request, ItemEntryCalculationService $calculationService): RedirectResponse
    {
        return $this->persist($request, $calculationService)
            ->with('status', 'Item entry is queued for saving.');
    }

    public function edit(ItemEntry $itemEntry): RedirectResponse
    {
        return redirect()->route('item-entries.index', ['edit' => $itemEntry->id]);
    }

    public function update(ItemEntryRequest $request, ItemEntryCalculationService $calculationService, ItemEntry $itemEntry): RedirectResponse
    {
        return $this->persist($request, $calculationService, $itemEntry)
            ->with('status', 'Item entry update is queued for saving.');
    }

    public function destroy(ItemEntry $itemEntry): RedirectResponse
    {
        $itemEntry->delete();

        return redirect()
            ->route('item-entries.index')
            ->with('status', 'Item entry deleted.');
    }

    private function persist(ItemEntryRequest $request, ItemEntryCalculationService $calculationService, ?ItemEntry $itemEntry = null): RedirectResponse
    {
        $validated = $request->validated();
        $uploadedPublicId = null;

        try {
            if ($request->hasFile('image')) {
                $upload = Cloudinary::uploadApi()->upload($request->file('image')->getRealPath(), [
                    'folder' => 'shams-print-crm/item-entries',
                    'resource_type' => 'image',
                ]);

                $validated['image_url'] = $upload['secure_url'];
                $uploadedPublicId = $upload['public_id'] ?? null;
            } elseif ($itemEntry) {
                $validated['image_url'] = $itemEntry->image_url;
            }

            $validated['total_amount'] = $calculationService->calculate($validated['pieces']);

            StoreItemEntryJob::dispatch([
                'lart_number' => $validated['lart_number'],
                'client_business_name' => $validated['client_business_name'],
                'description' => $validated['description'],
                'image_url' => $validated['image_url'],
                'darjan' => $validated['darjan'],
                'total_amount' => $validated['total_amount'],
            ], $validated['pieces'], $itemEntry?->id);

            return redirect()->route('item-entries.index');
        } catch (Throwable $exception) {
            if ($uploadedPublicId) {
                Cloudinary::uploadApi()->destroy($uploadedPublicId, ['resource_type' => 'image']);
            }

            report($exception);

            return back()
                ->withInput()
                ->withErrors(['image' => 'The item entry could not be saved. Please try again.']);
        }
    }
}

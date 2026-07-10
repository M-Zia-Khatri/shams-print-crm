<x-app-layout>
    @php
        $canManageItemEntries = in_array(auth()->user()?->role, ['admin', 'super_admin'], true);
        $editingEntry = $canManageItemEntries && request('edit')
            ? $itemEntries->firstWhere('id', (int) request('edit'))
            : null;
        $formEntry = $editingEntry;
        $showForm = $canManageItemEntries && (request('form') === 'create' || $editingEntry);
        $entryPayload = old('pieces') ? ['pieces' => old('pieces')] : ($formEntry ? [
            'id' => $formEntry->id,
            'lart_number' => $formEntry->lart_number,
            'client_business_name' => $formEntry->client_business_name,
            'description' => $formEntry->description,
            'darjan' => $formEntry->darjan,
            'pieces' => $formEntry->pieces->map(fn ($piece) => [
                'name' => $piece->name,
                'total_pieces' => $piece->total_pieces,
                'colors' => $piece->colors->map(fn ($color) => [
                    'type' => $color->type,
                    'rate' => $color->rate,
                    'type_color_count' => $color->type_color_count,
                ])->values(),
                'sizes' => $piece->sizes->map(fn ($size) => [
                    'size' => $size->size,
                    'percentage' => $size->percentage,
                ])->values(),
            ])->values(),
        ] : null);
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <x-section-title title="Item Entries" subtitle="Review and manage item-entry print orders." />

            @if ($canManageItemEntries)
                <a href="{{ route('item-entries.index', ['form' => 'create']) }}" class="btn btn-primary text-primary-content rounded-xl gap-2">
                    Add Entry
                </a>
            @endif
        </div>

        @if (session('status'))
            <div class="alert alert-success rounded-xl shadow-sm">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error rounded-xl shadow-sm items-start">
                <div>
                    <p class="font-bold">Please check your inputs:</p>
                    @foreach ($errors->all() as $error)
                        <p class="text-sm">&bull; {{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <x-dashboard-card title="Order Register" description="All submitted item-entry orders with server-calculated totals.">
            @if ($itemEntries->isEmpty())
                <x-empty-state title="No item entries yet" message="Create the first print order entry to start tracking work." />
            @else
                <div class="overflow-x-auto w-full">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Lart #</th>
                                <th>Party Name</th>
                                <th>Description</th>
                                <th>Darjan</th>
                                <th>Total</th>
                                <th>Created</th>
                                @if ($canManageItemEntries)
                                    <th class="text-right">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($itemEntries as $itemEntry)
                                <tr>
                                    <td class="font-semibold">{{ $itemEntry->lart_number }}</td>
                                    <td>{{ $itemEntry->client_business_name }}</td>
                                    <td class="max-w-xs truncate">{{ $itemEntry->description }}</td>
                                    <td>{{ $itemEntry->darjan }}</td>
                                    <td>{{ number_format((float) $itemEntry->total_amount, 2) }}</td>
                                    <td>{{ $itemEntry->created_at->format('M d, Y') }}</td>
                                    @if ($canManageItemEntries)
                                        <td>
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('item-entries.index', ['edit' => $itemEntry->id]) }}" class="btn btn-ghost btn-xs">Edit</a>
                                                <form method="POST" action="{{ route('item-entries.destroy', $itemEntry) }}" onsubmit="return confirm('Delete this item entry?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-error btn-xs text-error-content">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-dashboard-card>

        @if ($showForm)
            <div class="card bg-base-100 border border-base-300 shadow-sm rounded-2xl" id="item-entry-form-card">
                <div class="card-body p-6 sm:p-8">
                    <x-section-title :title="$formEntry ? 'Edit Entry' : 'Add Entry'" subtitle="Build pieces with independent color and size rows." />

                    <form method="POST" action="{{ $formEntry ? route('item-entries.update', $formEntry) : route('item-entries.store') }}" enctype="multipart/form-data" data-item-entry-form data-color-types='@json($colorTypes)' data-entry='@json($entryPayload)'>
                        @csrf
                        @if ($formEntry)
                            @method('PUT')
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="form-control">
                                <span class="label-text font-bold mb-1">Lart Number</span>
                                <input name="lart_number" class="input input-bordered rounded-xl" value="{{ old('lart_number', $formEntry?->lart_number) }}" required>
                            </label>

                            <label class="form-control">
                                <span class="label-text font-bold mb-1">Party Name</span>
                                <input name="client_business_name" class="input input-bordered rounded-xl" value="{{ old('client_business_name', $formEntry?->client_business_name) }}" required>
                            </label>

                            <label class="form-control md:col-span-2">
                                <span class="label-text font-bold mb-1">Description</span>
                                <input name="description" class="input input-bordered rounded-xl" value="{{ old('description', $formEntry?->description) }}" required>
                            </label>

                            <label class="form-control">
                                <span class="label-text font-bold mb-1">Image</span>
                                <input type="file" name="image" class="file-input file-input-bordered rounded-xl" accept="image/png,image/jpeg,image/webp" @required(! $formEntry)>
                                @if ($formEntry)
                                    <span class="label-text-alt mt-1">Leave blank to keep the current image.</span>
                                @endif
                            </label>

                            <label class="form-control">
                                <span class="label-text font-bold mb-1">Darjan</span>
                                <input type="number" name="darjan" min="0" class="input input-bordered rounded-xl" value="{{ old('darjan', $formEntry?->darjan) }}" required>
                            </label>
                        </div>

                        <div class="divider">Pieces</div>

                        <div class="space-y-4" data-pieces-container></div>

                        <div class="flex flex-col sm:flex-row justify-between gap-3 mt-6">
                            <button type="button" class="btn btn-outline rounded-xl" data-add-piece>Add Piece</button>
                            <div class="flex gap-2 justify-end">
                                <a href="{{ route('item-entries.index') }}" class="btn btn-ghost rounded-xl">Cancel</a>
                                <button type="submit" class="btn btn-primary text-primary-content rounded-xl">{{ $formEntry ? 'Queue Update' : 'Queue Save' }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

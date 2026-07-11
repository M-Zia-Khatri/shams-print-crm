<x-app-layout>
    @php
        $canManageItemEntries = in_array(auth()->user()?->role, ['super_admin', 'admin'], true);
        $sortUrl = function (string $sortKey) use ($filters): string {
            $nextDirection = ($filters['sort'] ?? 'date') === $sortKey && ($filters['direction'] ?? 'desc') === 'asc' ? 'desc' : 'asc';

            return route('item-entries.index', array_merge(request()->query(), [
                'sort' => $sortKey,
                'direction' => $nextDirection,
            ]));
        };
        $sortIndicator = function (string $sortKey) use ($filters): string {
            if (($filters['sort'] ?? 'date') !== $sortKey) {
                return '↕';
            }

            return ($filters['direction'] ?? 'desc') === 'asc' ? '↑' : '↓';
        };
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <x-section-title title="Item Entries" subtitle="Manage lart entries, party details, images, rates, and computed totals." />

        @if($canManageItemEntries)
            <form method="GET" action="{{ route('item-entries.create') }}">
                <x-action-button type="submit" variant="primary">Add Entry</x-action-button>
            </form>
        @endif
    </div>

    @if(session('status'))
        <div class="alert alert-success shadow-sm">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <x-dashboard-card title="Filters" description="Search item entries, filter by party or date, and sort the table server-side.">
        <form method="GET" action="{{ route('item-entries.index') }}" id="item-entries-filter-form" class="grid w-full grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4" data-item-entries-filter-form>
            <label class="form-control w-full">
                <span class="label-text font-semibold">Search</span>
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Lart, party, description, size" class="input input-bordered w-full" data-debounced-search>
            </label>

            <label class="form-control w-full">
                <span class="label-text font-semibold">Party Name</span>
                <select name="party_name" class="select select-bordered w-full" data-immediate-filter>
                    <option value="">All parties</option>
                    @foreach($partyNames as $partyName)
                        <option value="{{ $partyName }}" @selected($filters['party_name'] === $partyName)>{{ $partyName }}</option>
                    @endforeach
                </select>
            </label>

            <label class="form-control w-full">
                <span class="label-text font-semibold">Date</span>
                <select name="date_mode" class="select select-bordered w-full" data-date-mode-filter data-immediate-filter>
                    <option value="latest" @selected($filters['date_mode'] === 'latest')>Latest</option>
                    <option value="oldest" @selected($filters['date_mode'] === 'oldest')>Oldest</option>
                    <option value="monthly" @selected($filters['date_mode'] === 'monthly')>Monthly</option>
                </select>
            </label>

            <label class="form-control w-full {{ $filters['date_mode'] === 'monthly' ? '' : 'hidden' }}" data-month-filter-wrapper>
                <span class="label-text font-semibold">Month</span>
                <input type="month" name="month" value="{{ $filters['month'] }}" class="input input-bordered w-full" data-immediate-filter>
            </label>

            <div class="flex items-end gap-2 md:col-span-2 xl:col-span-4">
                <x-action-button type="submit" variant="primary">Apply Filters</x-action-button>
                <a href="{{ route('item-entries.index') }}" class="btn btn-ghost">Reset</a>
            </div>
        </form>
    </x-dashboard-card>

    <x-dashboard-card title="Entries" description="All item entries are listed below.">
        <div class="w-full overflow-x-auto">
            @if($itemEntries->isNotEmpty())
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th><a class="link-hover inline-flex items-center gap-1 font-semibold" href="{{ $sortUrl('date') }}">Date <span>{{ $sortIndicator('date') }}</span></a></th>
                            <th><a class="link-hover inline-flex items-center gap-1 font-semibold" href="{{ $sortUrl('lart_number') }}">Lart Number <span>{{ $sortIndicator('lart_number') }}</span></a></th>
                            <th><a class="link-hover inline-flex items-center gap-1 font-semibold" href="{{ $sortUrl('client_business_name') }}">Party Name <span>{{ $sortIndicator('client_business_name') }}</span></a></th>
                            <th><a class="link-hover inline-flex items-center gap-1 font-semibold" href="{{ $sortUrl('description') }}">Description <span>{{ $sortIndicator('description') }}</span></a></th>
                            <th>Image</th>
                            <th><a class="link-hover inline-flex items-center gap-1 font-semibold" href="{{ $sortUrl('darjan') }}">Darjan <span>{{ $sortIndicator('darjan') }}</span></a></th>
                            <th><a class="link-hover inline-flex items-center gap-1 font-semibold" href="{{ $sortUrl('total_color') }}">Total Color <span>{{ $sortIndicator('total_color') }}</span></a></th>
                            <th><a class="link-hover inline-flex items-center gap-1 font-semibold" href="{{ $sortUrl('total_rate') }}">Total Rate <span>{{ $sortIndicator('total_rate') }}</span></a></th>
                            <th><a class="link-hover inline-flex items-center gap-1 font-semibold" href="{{ $sortUrl('size_description') }}">Size <span>{{ $sortIndicator('size_description') }}</span></a></th>
                            <th><a class="link-hover inline-flex items-center gap-1 font-semibold" href="{{ $sortUrl('total_amount') }}">Total Amount <span>{{ $sortIndicator('total_amount') }}</span></a></th>
                            @if($canManageItemEntries)
                                <th class="text-right">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($itemEntries as $itemEntry)
                            <tr>
                                <td>{{ $itemEntry->created_at?->format('M d, Y') }}</td>
                                <td class="font-semibold">{{ $itemEntry->lart_number }}</td>
                                <td>{{ $itemEntry->client_business_name }}</td>
                                <td>{{ $itemEntry->description }}</td>
                                <td>
                                    <img src="{{ $itemEntry->image_url }}" alt="{{ $itemEntry->lart_number }}" class="h-14 w-14 rounded-lg object-cover border border-base-300">
                                </td>
                                <td>{{ $itemEntry->darjan }}</td>
                                <td>{{ $itemEntry->total_color }}</td>
                                <td>{{ number_format((float) $itemEntry->total_rate, 2) }}</td>
                                <td>{{ $itemEntry->size_description }}</td>
                                <td class="font-semibold">{{ number_format((float) $itemEntry->total_amount, 2) }}</td>
                                @if($canManageItemEntries)
                                    <td>
                                        <div class="flex justify-end gap-2">
                                            <form method="GET" action="{{ route('item-entries.edit', $itemEntry) }}">
                                                <x-action-button type="submit" variant="secondary" size="sm">Edit</x-action-button>
                                            </form>
                                            <form method="POST" action="{{ route('item-entries.destroy', $itemEntry) }}" onsubmit="return confirm('Delete this item entry?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-action-button type="submit" variant="error" size="sm">Delete</x-action-button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-bold">
                            <td colspan="9" class="text-right">Grand Total</td>
                            <td>{{ number_format((float) $grandTotal, 2) }}</td>
                            @if($canManageItemEntries)
                                <td></td>
                            @endif
                        </tr>
                    </tfoot>
                </table>

                <div class="mt-4">
                    {{ $itemEntries->links() }}
                </div>
            @else
                <x-empty-state title="No item entries found" message="No item entries match the current filters.">
                    <div class="flex flex-wrap justify-center gap-2">
                        <a href="{{ route('item-entries.index') }}" class="btn btn-ghost">Reset Filters</a>
                        @if($canManageItemEntries)
                            <form method="GET" action="{{ route('item-entries.create') }}">
                                <x-action-button type="submit" variant="primary">Add Entry</x-action-button>
                            </form>
                        @endif
                    </div>
                </x-empty-state>
            @endif
        </div>
    </x-dashboard-card>
</x-app-layout>

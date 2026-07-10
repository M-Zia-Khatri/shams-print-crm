<x-app-layout>
    @php
        $canManageItemEntries = in_array(auth()->user()?->role, ['super_admin', 'admin'], true);
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

    <x-dashboard-card title="Entries" description="All item entries are listed below.">
        <div class="w-full overflow-x-auto">
            @if($itemEntries->isNotEmpty())
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Lart Number</th>
                            <th>Party Name</th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Darjan</th>
                            <th>Total Color</th>
                            <th>Total Rate</th>
                            <th>Total Amount</th>
                            <th>Size</th>
                            @if($canManageItemEntries)
                                <th class="text-right">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($itemEntries as $itemEntry)
                            <tr>
                                <td class="font-semibold">{{ $itemEntry->lart_number }}</td>
                                <td>{{ $itemEntry->client_business_name }}</td>
                                <td>{{ $itemEntry->description }}</td>
                                <td>
                                    <img src="{{ $itemEntry->image_url }}" alt="{{ $itemEntry->lart_number }}" class="h-14 w-14 rounded-lg object-cover border border-base-300">
                                </td>
                                <td>{{ $itemEntry->darjan }}</td>
                                <td>{{ $itemEntry->total_color }}</td>
                                <td>{{ number_format((float) $itemEntry->total_rate, 2) }}</td>
                                <td>{{ number_format((float) $itemEntry->total_amount, 2) }}</td>
                                <td>{{ $itemEntry->size_description }}</td>
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
                </table>

                <div class="mt-4">
                    {{ $itemEntries->links() }}
                </div>
            @else
                <x-empty-state title="No item entries found" message="Create the first item entry to start tracking production details.">
                    @if($canManageItemEntries)
                        <form method="GET" action="{{ route('item-entries.create') }}">
                            <x-action-button type="submit" variant="primary">Add Entry</x-action-button>
                        </form>
                    @endif
                </x-empty-state>
            @endif
        </div>
    </x-dashboard-card>
</x-app-layout>

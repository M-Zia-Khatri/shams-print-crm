<x-app-layout>
    @php
        $canManageItemEntries = in_array(auth()->user()?->role, ['super_admin', 'admin'], true);
        $sortUrl = function (string $sortKey) use ($filters): string {
            $nextDirection =
                ($filters['sort'] ?? 'date') === $sortKey && ($filters['direction'] ?? 'desc') === 'asc'
                    ? 'desc'
                    : 'asc';

            return route(
                'item-entries.index',
                array_merge(request()->query(), [
                    'sort' => $sortKey,
                    'direction' => $nextDirection,
                ]),
            );
        };
        $sortIndicator = function (string $sortKey) use ($filters): string {
            if (($filters['sort'] ?? 'date') !== $sortKey) {
                return '↕';
            }

            return ($filters['direction'] ?? 'desc') === 'asc' ? '↑' : '↓';
        };
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <x-section-title title="Item Entries & Payments"
            subtitle="Manage lart entries, party details, received payments, and computed totals." />

        @if ($canManageItemEntries)
            <div class="flex gap-2">
                <x-action-button type="button" variant="secondary" onclick="window.openPaymentModal()">Add Received
                    Payment</x-action-button>

                <form method="GET" action="{{ route('item-entries.create') }}">
                    <x-action-button type="submit" variant="primary">Add Entry</x-action-button>
                </form>
            </div>
        @endif
    </div>

    @if (session('status'))
        <div class="alert alert-success shadow-sm">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <div class="stats shadow w-full mb-4">
        <div class="stat">
            <div class="stat-title">Previous Balance</div>
            <div class="stat-value text-base-content">{{ number_format((float) $previousBalance, 2) }}</div>
            <div class="stat-desc">For selected filters</div>
        </div>
        <div class="stat">
            <div class="stat-title">Grand Total</div>
            <div class="stat-value text-primary">{{ number_format((float) $grandTotal, 2) }}</div>
            <div class="stat-desc">Prev. Balance + Entries - Payments</div>
        </div>
    </div>

    <x-dashboard-card title="Filters"
        description="Search item entries, filter by party or date, and sort the table server-side.">
        <form method="GET" action="{{ route('item-entries.index') }}" id="item-entries-filter-form"
            class="grid w-full grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4" data-item-entries-filter-form>
            <label class="form-control w-full">
                <span class="label-text font-semibold">Search</span>
                <input type="search" name="search" value="{{ $filters['search'] }}"
                    placeholder="Lart, party, description, size" class="input input-bordered w-full"
                    data-debounced-search>
            </label>

            <label class="form-control w-full">
                <span class="label-text font-semibold">Party Name</span>
                <select name="party_name" class="select select-bordered w-full" data-immediate-filter>
                    <option value="">All parties</option>
                    @foreach ($partyNames as $partyName)
                        <option value="{{ $partyName }}" @selected($filters['party_name'] === $partyName)>{{ $partyName }}</option>
                    @endforeach
                </select>
            </label>

            <label class="form-control w-full">
                <span class="label-text font-semibold">Date</span>
                <select name="date_mode" class="select select-bordered w-full" data-date-mode-filter
                    data-immediate-filter>
                    <option value="latest" @selected($filters['date_mode'] === 'latest')>Latest</option>
                    <option value="oldest" @selected($filters['date_mode'] === 'oldest')>Oldest</option>
                    <option value="monthly" @selected($filters['date_mode'] === 'monthly')>Monthly</option>
                </select>
            </label>

            <label class="form-control w-full {{ $filters['date_mode'] === 'monthly' ? '' : 'hidden' }}"
                data-month-filter-wrapper>
                <span class="label-text font-semibold">Month</span>
                <input type="month" name="month" value="{{ $filters['month'] }}" class="input input-bordered w-full"
                    data-immediate-filter>
            </label>

            <div class="flex items-end gap-2 md:col-span-2 xl:col-span-4">
                <x-action-button type="submit" variant="primary">Apply Filters</x-action-button>
                <a href="{{ route('item-entries.index') }}" class="btn btn-ghost">Reset</a>
            </div>
        </form>
    </x-dashboard-card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-6">
        <div class="lg:col-span-2">
            <x-dashboard-card title="Entries" description="All item entries are listed below.">
                <div class="w-full overflow-x-auto">
                    @if ($itemEntries->isNotEmpty())
                        <table class="table table-zebra w-full">
                            <thead>
                                <tr>
                                    <th><a class="link-hover inline-flex items-center gap-1 font-semibold"
                                            href="{{ $sortUrl('lart_number') }}">Lart
                                            <span>{{ $sortIndicator('lart_number') }}</span></a></th>
                                    <th><a class="link-hover inline-flex items-center gap-1 font-semibold"
                                            href="{{ $sortUrl('client_business_name') }}">Party
                                            <span>{{ $sortIndicator('client_business_name') }}</span></a></th>
                                    <th><a class="link-hover inline-flex items-center gap-1 font-semibold w-32"
                                            href="{{ $sortUrl('description') }}">Description
                                            <span>{{ $sortIndicator('description') }}</span></a></th>
                                    <th>Image</th>
                                    <th><a class="link-hover inline-flex items-center gap-1 font-semibold"
                                            href="{{ $sortUrl('darjan') }}">Darjan
                                            <span>{{ $sortIndicator('darjan') }}</span></a></th>
                                    <th><a class="link-hover inline-flex items-center gap-1 font-semibold"
                                            href="{{ $sortUrl('total_color') }}">Color
                                            <span>{{ $sortIndicator('total_color') }}</span></a></th>
                                    <th><a class="link-hover inline-flex items-center gap-1 font-semibold"
                                            href="{{ $sortUrl('total_rate') }}">T.Rate
                                            <span>{{ $sortIndicator('total_rate') }}</span></a></th>
                                    <th><a class="link-hover inline-flex items-center gap-1 font-semibold w-18"
                                            href="{{ $sortUrl('size_description') }}">Size
                                            <span>{{ $sortIndicator('size_description') }}</span></a></th>
                                    <th><a class="link-hover inline-flex items-center gap-1 font-semibold"
                                            href="{{ $sortUrl('total_amount') }}">Amount
                                            <span>{{ $sortIndicator('total_amount') }}</span></a></th>
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
                                        <td>{{ $itemEntry->description }}</td>
                                        <td>
                                            <img src="{{ $itemEntry->image_url }}" alt="{{ $itemEntry->lart_number }}"
                                                class="h-14 w-14 rounded-lg object-cover border border-base-300">
                                        </td>
                                        <td>{{ $itemEntry->darjan }}</td>
                                        <td>{{ $itemEntry->total_color }}</td>
                                        <td>{{ number_format((float) $itemEntry->total_rate, 2) }}</td>
                                        <td>{{ $itemEntry->size_description }}</td>
                                        <td class="font-semibold">
                                            {{ number_format((float) $itemEntry->total_amount, 2) }}</td>
                                        @if ($canManageItemEntries)
                                            <td>
                                                <div class="flex justify-end gap-2">
                                                    <form method="GET"
                                                        action="{{ route('item-entries.edit', $itemEntry) }}">
                                                        <x-action-button type="submit" variant="secondary"
                                                            size="sm"><x-feathericon-edit
                                                                class="w-4 h-4" /></x-action-button>
                                                    </form>
                                                    <form method="POST"
                                                        action="{{ route('item-entries.destroy', $itemEntry) }}"
                                                        onsubmit="return confirm('Delete this item entry?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-action-button type="submit" variant="error"
                                                            size="sm"><x-heroicon-o-trash
                                                                class="w-4 h-4" /></x-action-button>
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
                        <x-empty-state title="No item entries found"
                            message="No item entries match the current filters.">
                            <div class="flex flex-wrap justify-center gap-2">
                                <a href="{{ route('item-entries.index') }}" class="btn btn-ghost">Reset Filters</a>
                                @if ($canManageItemEntries)
                                    <form method="GET" action="{{ route('item-entries.create') }}">
                                        <x-action-button type="submit" variant="primary">Add Entry</x-action-button>
                                    </form>
                                @endif
                            </div>
                        </x-empty-state>
                    @endif
                </div>
            </x-dashboard-card>
        </div>

        <div>
            <x-dashboard-card title="Received Payments"
                description="History of received payments for selected filters.">
                <div class="w-full overflow-x-auto">
                    @if ($receivedPayments->isNotEmpty())
                        <table class="table table-sm w-full table-zebra">
                            <thead>
                                <tr>
                                    <th>Party</th>
                                    <th>Amount</th>
                                    @if ($canManageItemEntries)
                                        <th class="text-right">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($receivedPayments as $payment)
                                    <tr>
                                        <td>
                                            <div class="font-semibold truncate max-w-[120px]"
                                                title="{{ $payment->party_name }}">
                                                {{ $payment->party_name }}
                                            </div>
                                            <div class="text-xs text-base-content/70 truncate max-w-[120px]"
                                                title="{{ $payment->description }}">
                                                {{ $payment->description }}
                                            </div>
                                        </td>
                                        <td class="font-bold text-success w-[80px]">
                                            +{{ number_format((float) $payment->received_amount, 2) }}
                                        </td>
                                        @if ($canManageItemEntries)
                                            <td class="w-[100px]">
                                                <div class="flex justify-end gap-1">
                                                    <button type="button" class="btn btn-xs btn-outline"
                                                        onclick="window.editPaymentModal({{ $payment->id }}, '{{ addslashes($payment->description) }}', '{{ addslashes($payment->party_name) }}', {{ $payment->received_amount }})"><x-feathericon-edit
                                                            class="w-4 h-4" /></button>
                                                    <button type="button" class="btn btn-xs btn-outline btn-error"
                                                        onclick="window.deletePayment({{ $payment->id }})"><x-heroicon-o-trash
                                                            class="w-4 h-4" /></button>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-4 text-base-content/60">
                            No received payments found.
                        </div>
                    @endif
                </div>
            </x-dashboard-card>
        </div>
    </div>

    @if ($canManageItemEntries)
        <dialog id="receivedPaymentModal" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg" id="modalTitle">Add Received Payment</h3>
                <form id="paymentForm" onsubmit="window.submitPaymentForm(event)">
                    @csrf
                    <input type="hidden" name="payment_id" id="paymentId">
                    <div class="form-control mb-4 mt-4">
                        <label class="label"><span class="label-text font-semibold">Description</span></label>
                        <input type="text" name="description" id="paymentDescription"
                            class="input input-bordered w-full" required>
                    </div>
                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text font-semibold">Party Name</span></label>
                        <select name="party_name" id="paymentPartyName" class="select select-bordered w-full"
                            required>
                            <option value="" disabled selected>Select Party</option>
                            @foreach ($partyNames as $name)
                                <option value="{{ $name }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control mb-6">
                        <label class="label"><span class="label-text font-semibold">Received Amount</span></label>
                        <input type="number" step="0.01" min="0" name="received_amount"
                            id="paymentAmount" class="input input-bordered w-full" required>
                    </div>
                    <div class="modal-action">
                        <button type="button" class="btn"
                            onclick="document.getElementById('receivedPaymentModal').close()">Cancel</button>
                        <x-action-button type="submit" variant="primary" id="savePaymentBtn">Save
                            Payment</x-action-button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>
    @endif

    @push('scripts')
        <script>
            window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector(
                'input[name="_token"]')?.value;
        </script>
        @vite('resources/js/item-entries-index.js')
    @endpush
</x-app-layout>

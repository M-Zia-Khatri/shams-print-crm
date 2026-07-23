<x-app-layout>
    @php
        $PREVIOUS_BALANCES = [
            'super garment' => 40000.0,
            'zia' => 1000.0,
        ];

        $previousBalance = array_sum($PREVIOUS_BALANCES);

        $totalAmountSum = App\Models\ItemEntry::sum('total_amount');
        $receivedAmountSum = App\Models\ItemPaymentReceived::sum('received_amount');

        $grandTotal = $previousBalance + $totalAmountSum - $receivedAmountSum;

        $entryTotalsByParty = App\Models\ItemEntry::query()
            ->select('client_business_name')
            ->selectRaw('SUM(total_amount) as total_amount_sum')
            ->groupBy('client_business_name')
            ->pluck('total_amount_sum', 'client_business_name');

        $receivedTotalsByParty = App\Models\ItemPaymentReceived::query()
            ->select('party_name')
            ->selectRaw('SUM(received_amount) as received_amount_sum')
            ->groupBy('party_name')
            ->pluck('received_amount_sum', 'party_name');

        $partyNames = $entryTotalsByParty->keys()->merge($receivedTotalsByParty->keys())->unique()->sort()->values();

        $partyRows = $partyNames->map(function ($name) use (
            $entryTotalsByParty,
            $receivedTotalsByParty,
            $PREVIOUS_BALANCES,
        ) {
            $previous = $PREVIOUS_BALANCES[strtolower($name)] ?? 0.0;
            $entries = (float) ($entryTotalsByParty[$name] ?? 0);
            $received = (float) ($receivedTotalsByParty[$name] ?? 0);

            return [
                'name' => $name,
                'total' => $previous + $entries - $received,
            ];
        });
    @endphp

    <div class="p-4 space-y-6">
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">Grand Total</div>
                <div class="stat-value">{{ number_format($grandTotal, 2) }}</div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Party Name</th>
                        <th class="text-right">Party Grand Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($partyRows as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-right">{{ number_format($row['total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center">No parties found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

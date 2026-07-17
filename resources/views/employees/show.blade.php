<x-app-layout>
    <div class="flex gap-4 items-center mb-6">
        <x-section-title title="{{ $employee->name }}" subtitle="Employee overview, financials, and timeline." />
        @if (in_array(auth()->user()?->role, ['super_admin', 'admin'], true))
            <div class="flex gap-2">
                <a href="{{ route('employees.edit', $employee) }}">
                    <x-action-button type="submit" variant="secondary">Edit</x-action-button>
                </a>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <x-dashboard-card title="Role" description="Employee role">
            <div class="text-lg font-semibold">{{ $employee->role->label() }}</div>
        </x-dashboard-card>
        <x-dashboard-card title="Daily Laberi" description="Rate per full day">
            <div class="text-lg font-semibold">{{ number_format((float) $employee->daily_laberi, 2) }}</div>
        </x-dashboard-card>
        <x-dashboard-card title="Phone" description="Contact number">
            <div class="text-lg font-semibold">{{ $employee->phone_number }}</div>
        </x-dashboard-card>
        <x-dashboard-card title="Status" description="Current status">
            <span class="badge {{ $employee->status->value === 'active' ? 'badge-success' : 'badge-ghost' }}">{{ $employee->status->label() }}</span>
        </x-dashboard-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <x-dashboard-card title="This Week" description="Financial summary for the current week">
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-center">
                <div><div class="text-xs text-base-content/60">Earned</div><div class="font-bold">{{ number_format($currentWeekSummary['total_earned'], 2) }}</div></div>
                <div><div class="text-xs text-base-content/60">Advance</div><div class="font-bold">{{ number_format($currentWeekSummary['total_advance'], 2) }}</div></div>
                <div><div class="text-xs text-base-content/60">Bonus</div><div class="font-bold">{{ number_format($currentWeekSummary['total_bonus'], 2) }}</div></div>
                <div><div class="text-xs text-base-content/60">Paid</div><div class="font-bold">{{ number_format($currentWeekSummary['total_paid'], 2) }}</div></div>
                <div><div class="text-xs text-base-content/60">Remaining</div><div class="font-bold">{{ number_format($currentWeekSummary['remaining'], 2) }}</div></div>
            </div>
        </x-dashboard-card>
        <x-dashboard-card title="Lifetime" description="Financial summary since joining">
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-center">
                <div><div class="text-xs text-base-content/60">Earned</div><div class="font-bold">{{ number_format($lifetimeSummary['total_earned'], 2) }}</div></div>
                <div><div class="text-xs text-base-content/60">Advance</div><div class="font-bold">{{ number_format($lifetimeSummary['total_advance'], 2) }}</div></div>
                <div><div class="text-xs text-base-content/60">Bonus</div><div class="font-bold">{{ number_format($lifetimeSummary['total_bonus'], 2) }}</div></div>
                <div><div class="text-xs text-base-content/60">Paid</div><div class="font-bold">{{ number_format($lifetimeSummary['total_paid'], 2) }}</div></div>
                <div><div class="text-xs text-base-content/60">Remaining</div><div class="font-bold">{{ number_format($lifetimeSummary['remaining'], 2) }}</div></div>
            </div>
        </x-dashboard-card>
    </div>

    <x-dashboard-card title="Shift Summary (This Week)" description="Breakdown of shift types">
        <div class="grid grid-cols-5 gap-2 text-center">
            @foreach ($shiftBreakdown as $shift => $count)
                <div><div class="text-xs text-base-content/60 capitalize">{{ $shift }}</div><div class="font-bold">{{ $count }}</div></div>
            @endforeach
        </div>
    </x-dashboard-card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
        <x-dashboard-card title="Recent Shifts" description="Latest recorded shifts">
            <div class="w-full overflow-x-auto">
                <table class="table table-sm w-full">
                    <thead><tr><th>Date</th><th>Shift</th></tr></thead>
                    <tbody>
                        @foreach ($recentShifts as $shift)
                            <tr><td>{{ $shift->laberi_date->format('M d, Y') }}</td><td>{{ $shift->daily_shift->label() }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-dashboard-card>
        <x-dashboard-card title="Recent Payments" description="Latest recorded payments">
            <div class="w-full overflow-x-auto">
                <table class="table table-sm w-full">
                    <thead><tr><th>Date</th><th>Type</th><th>Amount</th></tr></thead>
                    <tbody>
                        @foreach ($recentPayments as $payment)
                            <tr><td>{{ $payment->paid_date->format('M d, Y') }}</td><td>{{ $payment->payment_type->label() }}</td><td>{{ number_format((float) $payment->amount, 2) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-dashboard-card>
    </div>

    <x-dashboard-card title="Timeline" description="Combined shift and payment history">
        <x-date-range-filter :start-date="$filters['start_date']" :end-date="$filters['end_date']" range="custom" />

        <div class="w-full overflow-x-auto mt-4">
            <table class="table table-sm w-full table-zebra">
                <thead><tr><th>Date</th><th>Type</th><th>Detail</th></tr></thead>
                <tbody>
                    @foreach ($timeline as $item)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($item['date'])->format('M d, Y') }}</td>
                            <td class="capitalize">{{ $item['type'] }}</td>
                            <td>{{ $item['label'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-dashboard-card>
</x-app-layout>

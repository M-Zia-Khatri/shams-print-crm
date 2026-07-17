<x-app-layout>
    <x-section-title title="Weekly Payroll Summary" subtitle="Review, lock, and pay weekly payroll." />

    @if (session('status'))
        <div class="alert alert-success shadow-sm">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <x-dashboard-card title="Filters" description="Select the payroll week">
        <x-date-range-filter :start-date="$filters['start_date']" :end-date="$filters['end_date']" range="custom" :action="route('employee-payroll.index')" />
    </x-dashboard-card>

    <x-dashboard-card title="Payroll ({{ \Illuminate\Support\Carbon::parse($filters['start_date'])->format('M d') }} - {{ \Illuminate\Support\Carbon::parse($filters['end_date'])->format('M d, Y') }})"
        description="{{ $lock ? 'This week is locked.' : 'This week is unlocked.' }}">

        @if (in_array(auth()->user()?->role, ['super_admin', 'admin'], true))
            <div class="mb-4 flex gap-2">
                @if ($lock)
                    <form method="POST" action="{{ route('employee-payroll.unlock') }}">
                        @csrf
                        <input type="hidden" name="week_start_date" value="{{ $filters['start_date'] }}">
                        <input type="hidden" name="week_end_date" value="{{ $filters['end_date'] }}">
                        <x-action-button type="submit" variant="warning">Unlock Week</x-action-button>
                    </form>
                @else
                    <form method="POST" action="{{ route('employee-payroll.lock') }}">
                        @csrf
                        <input type="hidden" name="week_start_date" value="{{ $filters['start_date'] }}">
                        <input type="hidden" name="week_end_date" value="{{ $filters['end_date'] }}">
                        <x-action-button type="submit" variant="error">Lock Week</x-action-button>
                    </form>
                @endif
                <a href="{{ route('employee-paid-laberi.bulk-create') }}">
                    <x-action-button type="submit" variant="secondary">Bulk Payment</x-action-button>
                </a>
            </div>
        @endif

        <div class="w-full overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Working Days</th>
                        <th>Earned</th>
                        <th>Paid</th>
                        <th>Remaining</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td class="font-semibold">{{ $row['employee']->name }}</td>
                            <td>{{ $row['working_days'] }}</td>
                            <td>{{ number_format($row['total_earned'], 2) }}</td>
                            <td>{{ number_format($row['total_paid'], 2) }}</td>
                            <td class="font-semibold">{{ number_format($row['remaining'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-dashboard-card>
</x-app-layout>

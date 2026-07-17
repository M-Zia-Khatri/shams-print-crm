<x-app-layout>
    <x-section-title title="Payroll Summary" subtitle="Review, lock, and pay payroll for the selected period." />

    @if (session('status'))
        <div class="alert alert-success shadow-sm">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <x-dashboard-card title="Filters" description="Select the payroll period">
        <x-date-range-filter
            :start-date="$filters['start_date']"
            :end-date="$filters['end_date']"
            :range="$filters['range']"
            :action="route('employee-payroll.index')"
        />
    </x-dashboard-card>

    <x-dashboard-card
        title="Payroll ({{ \Illuminate\Support\Carbon::parse($filters['start_date'])->format('M d') }} – {{ \Illuminate\Support\Carbon::parse($filters['end_date'])->format('M d, Y') }})"
        description="{{ $lock ? 'This period overlaps a locked payroll week.' : 'This period is unlocked.' }}"
    >
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <span class="badge badge-outline">Payroll Type: {{ $payrollType->label() }}</span>
        </div>

        @if (in_array(auth()->user()?->role, ['super_admin', 'admin'], true))
            <div class="mb-4 flex flex-wrap gap-2">
                @if ($exactLock)
                    <form method="POST" action="{{ route('employee-payroll.unlock') }}">
                        @csrf
                        <input type="hidden" name="week_start_date" value="{{ $filters['start_date'] }}">
                        <input type="hidden" name="week_end_date" value="{{ $filters['end_date'] }}">
                        <x-action-button type="submit" variant="warning">Unlock Week</x-action-button>
                    </form>
                @elseif (! $lock)
                    <form method="POST" action="{{ route('employee-payroll.lock') }}">
                        @csrf
                        <input type="hidden" name="week_start_date" value="{{ $filters['start_date'] }}">
                        <input type="hidden" name="week_end_date" value="{{ $filters['end_date'] }}">
                        <x-action-button type="submit" variant="error">Lock Week</x-action-button>
                    </form>
                @endif

                @if (! $lock)
                    <a href="{{ route('employee-paid-laberi.bulk-create') }}" class="btn btn-secondary">Bulk Payment</a>
                @endif
            </div>
        @endif

        <div class="w-full overflow-x-auto">
            @if ($rows->isNotEmpty())
                <table class="table table-zebra w-full min-w-[56rem]">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Working Days</th>
                            <th>Earned</th>
                            <th>Advance</th>
                            <th>Bonus</th>
                            <th>Paid</th>
                            <th>Remaining</th>
                            <th>Payroll Type</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td class="font-semibold">
                                    <a href="{{ route('employees.show', $row['employee']) }}" class="link link-hover">
                                        {{ $row['employee']->name }}
                                    </a>
                                </td>
                                <td>{{ $row['working_days'] }}</td>
                                <td>{{ number_format($row['total_earned'], 2) }}</td>
                                <td>{{ number_format($row['total_advance'], 2) }}</td>
                                <td>{{ number_format($row['total_bonus'], 2) }}</td>
                                <td>{{ number_format($row['total_paid'], 2) }}</td>
                                <td class="font-semibold">{{ number_format($row['remaining'], 2) }}</td>
                                <td>{{ $payrollType->label() }}</td>
                                <td>
                                    <div class="flex justify-end">
                                        <a href="{{ route('employees.show', $row['employee']) }}" class="btn btn-ghost btn-sm">View</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <x-empty-state title="No employees" message="There are no active employees for this payroll period." />
            @endif
        </div>
    </x-dashboard-card>
</x-app-layout>

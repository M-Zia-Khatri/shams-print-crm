<x-app-layout>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <x-section-title title="{{ $employee->name }} — Daily Laberi" subtitle="Shift history for this employee." />
        @if (in_array(auth()->user()?->role, ['super_admin', 'admin'], true))
            <a href="{{ route('employees.daily-laberi.create', $employee) }}">
                <x-action-button type="submit" variant="primary">Add Shift Entry</x-action-button>
            </a>
        @endif
    </div>

    @if (session('status'))
        <div class="alert alert-success shadow-sm">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <x-dashboard-card title="Filters" description="Filter shift entries by date range">
        <x-date-range-filter :start-date="$filters['start_date']" :end-date="$filters['end_date']" range="custom" />
    </x-dashboard-card>

    <x-dashboard-card title="Shift Entries" description="All recorded shifts.">
        <div class="w-full overflow-x-auto">
            @if ($entries->isNotEmpty())
                <table class="table table-zebra w-full">
                    <thead><tr><th>Date</th><th>Shift</th></tr></thead>
                    <tbody>
                        @foreach ($entries as $entry)
                            <tr><td>{{ $entry->laberi_date->format('M d, Y') }}</td><td>{{ $entry->daily_shift->label() }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">{{ $entries->links() }}</div>
            @else
                <x-empty-state title="No shift entries found" message="No entries match the current filters." />
            @endif
        </div>
    </x-dashboard-card>
</x-app-layout>

@props([
    'action' => url()->current(),
    'startDate' => '',
    'endDate' => '',
    'range' => 'current',
])

<form method="GET" action="{{ $action }}" class="flex flex-wrap items-end gap-3" data-date-range-filter>
    @foreach (request()->except(['range', 'start_date', 'end_date', 'page']) as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach

    <label class="form-control">
        <span class="label-text font-semibold text-sm">Range</span>
        <select name="range" class="select select-bordered select-sm" data-range-select>
            <option value="today" @selected($range === 'today')>Today</option>
            <option value="current" @selected($range === 'current')>This Week</option>
            <option value="last" @selected($range === 'last')>Last Week</option>
            <option value="month" @selected($range === 'month')>This Month</option>
            <option value="custom" @selected($range === 'custom')>Custom Range</option>
        </select>
    </label>

    <label class="form-control {{ $range === 'custom' ? '' : 'hidden' }}" data-custom-range-field>
        <span class="label-text font-semibold text-sm">Start Date</span>
        <input type="date" name="start_date" value="{{ $startDate }}" class="input input-bordered input-sm">
    </label>

    <label class="form-control {{ $range === 'custom' ? '' : 'hidden' }}" data-custom-range-field>
        <span class="label-text font-semibold text-sm">End Date</span>
        <input type="date" name="end_date" value="{{ $endDate }}" class="input input-bordered input-sm">
    </label>

    <x-action-button type="submit" variant="primary" size="sm">Apply</x-action-button>
</form>

<script>
    (function () {
        document.querySelectorAll('[data-date-range-filter]').forEach((form) => {
            const rangeSelect = form.querySelector('[data-range-select]');
            const customFields = form.querySelectorAll('[data-custom-range-field]');

            if (!rangeSelect) return;

            rangeSelect.addEventListener('change', () => {
                const isCustom = rangeSelect.value === 'custom';
                customFields.forEach((field) => field.classList.toggle('hidden', !isCustom));
            });
        });
    })();
</script>

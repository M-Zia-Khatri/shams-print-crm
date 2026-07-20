<x-app-layout>
    <x-section-title title="Add Shift Entry" subtitle="Record a single shift for {{ $employee->name }}." />

    @if ($errors->any())
        <div class="alert alert-error shadow-sm mb-4">
            <span>Please review the highlighted fields and try again.</span>
        </div>
    @endif

    <x-dashboard-card title="{{ $employee->name }}" description="Enter shift details below.">
        <form method="POST" action="{{ route('employees.daily-laberi.store', $employee) }}" class="w-full space-y-4">
            @csrf

            <label class="form-control w-full">
                <span class="label-text font-semibold">Date</span>
                <input type="date" name="laberi_date" value="{{ old('laberi_date', now()->toDateString()) }}" class="input input-bordered w-full" required>
            </label>

            <label class="form-control w-full">
                <span class="label-text font-semibold">Shift</span>
                <select name="daily_shift" class="select select-bordered w-full" required>
                    @foreach ($shifts as $shift)
                        <option value="{{ $shift->value }}" @selected(old('daily_shift') === $shift->value)>{{ $shift->label() }}</option>
                    @endforeach
                </select>
            </label>

            <div class="flex justify-end gap-2">
                <a href="{{ route('employees.daily-laberi.index', $employee) }}" class="btn btn-ghost">Cancel</a>
                <x-action-button type="submit" variant="primary">Save Entry</x-action-button>
            </div>
        </form>
    </x-dashboard-card>
</x-app-layout>

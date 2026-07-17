<x-app-layout>
    <x-section-title title="Add Shift" subtitle="Record daily shifts for all active employees." />

    @if ($errors->any())
        <div class="alert alert-error shadow-sm mb-4">
            <span>Please review the highlighted fields and try again.</span>
        </div>
    @endif

    <x-dashboard-card title="Shift Entry" description="Unchecked employees are recorded as Leave.">
        <form method="POST" action="{{ route('employees.shifts.store') }}" class="w-full space-y-4" id="shift-form">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="form-control w-full">
                    <span class="label-text font-semibold">Shift Date</span>
                    <input type="date" name="shift_date" value="{{ old('shift_date', now()->toDateString()) }}" class="input input-bordered w-full" required>
                </label>

                <label class="form-control w-full">
                    <span class="label-text font-semibold">Default Shift</span>
                    <select name="default_shift" id="default-shift-select" class="select select-bordered w-full" required>
                        @foreach ($shifts as $shift)
                            <option value="{{ $shift->value }}" @selected(old('default_shift') === $shift->value)>{{ $shift->label() }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <label class="flex items-center gap-2 cursor-pointer w-fit">
                <input type="checkbox" id="advanced-mode-toggle" class="checkbox">
                <span class="label-text font-semibold">Advanced mode (per-employee shift override)</span>
            </label>

            <div class="w-full overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Include</th>
                            <th>Employee</th>
                            <th data-advanced-column class="hidden">Shift</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                            <tr>
                                <td>
                                    <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" class="checkbox" checked data-employee-checkbox>
                                </td>
                                <td>{{ $employee->name }}</td>
                                <td data-advanced-column class="hidden">
                                    <select name="overrides[{{ $employee->id }}]" class="select select-bordered select-sm w-full" data-employee-override>
                                        @foreach ($shifts as $shift)
                                            <option value="{{ $shift->value }}">{{ $shift->label() }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('employees.index') }}" class="btn btn-ghost">Cancel</a>
                <x-action-button type="submit" variant="primary">Save Shifts</x-action-button>
            </div>
        </form>
    </x-dashboard-card>

    @vite('resources/js/employee-shifts-create.js')
</x-app-layout>

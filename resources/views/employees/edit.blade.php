<x-app-layout>
    <x-section-title title="Edit Employee" subtitle="Update employee details." />

    @if ($errors->any())
        <div class="alert alert-error shadow-sm mb-4">
            <span>Please review the highlighted fields and try again.</span>
        </div>
    @endif

    <x-dashboard-card title="{{ $employee->name }}" description="Update details below.">
        <form method="POST" action="{{ route('employees.update', $employee) }}" class="w-full space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="form-control w-full">
                    <span class="label-text font-semibold">Name</span>
                    <input type="text" name="name" value="{{ old('name', $employee->name) }}" class="input input-bordered w-full" required maxlength="255">
                </label>

                <label class="form-control w-full">
                    <span class="label-text font-semibold">Daily Laberi</span>
                    <input type="number" step="0.01" min="0" name="daily_laberi" value="{{ old('daily_laberi', $employee->daily_laberi) }}" class="input input-bordered w-full" required>
                </label>

                <label class="form-control w-full">
                    <span class="label-text font-semibold">Role</span>
                    <select name="role" class="select select-bordered w-full" required>
                        @foreach (\App\Enums\EmployeeRole::cases() as $roleOption)
                            <option value="{{ $roleOption->value }}" @selected(old('role', $employee->role->value) === $roleOption->value)>{{ $roleOption->label() }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="form-control w-full">
                    <span class="label-text font-semibold">Phone Number</span>
                    <input type="text" name="phone_number" value="{{ old('phone_number', $employee->phone_number) }}" class="input input-bordered w-full" required maxlength="255">
                </label>

                <label class="form-control w-full">
                    <span class="label-text font-semibold">Status</span>
                    <select name="status" class="select select-bordered w-full">
                        @foreach (\App\Enums\EmployeeStatus::cases() as $statusOption)
                            <option value="{{ $statusOption->value }}" @selected(old('status', $employee->status->value) === $statusOption->value)>{{ $statusOption->label() }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="form-control w-full">
                    <span class="label-text font-semibold">Joining Date</span>
                    <input type="date" name="joining_date" value="{{ old('joining_date', $employee->joining_date->format('Y-m-d')) }}" class="input input-bordered w-full" required>
                </label>

                <label class="form-control w-full md:col-span-2">
                    <span class="label-text font-semibold">Notes</span>
                    <textarea name="notes" class="textarea textarea-bordered w-full">{{ old('notes', $employee->notes) }}</textarea>
                </label>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('employees.index') }}" class="btn btn-ghost">Cancel</a>
                <x-action-button type="submit" variant="primary">Update Employee</x-action-button>
            </div>
        </form>
    </x-dashboard-card>
</x-app-layout>

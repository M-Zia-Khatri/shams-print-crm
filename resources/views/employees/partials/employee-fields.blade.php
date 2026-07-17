@props(['prefix' => '', 'index' => 0, 'employee' => null])

@php
    $field = fn (string $name) => "{$prefix}[{$name}]";
@endphp

<div class="employee-fields grid grid-cols-1 md:grid-cols-2 gap-4 rounded-xl border border-base-300 bg-base-100/60 p-4">
    <label class="form-control w-full">
        <span class="label-text font-semibold">Name</span>
        <input type="text" name="{{ $field('name') }}" value="{{ old("employees.{$index}.name", $employee?->name) }}" class="input input-bordered w-full" required maxlength="255">
    </label>

    <label class="form-control w-full">
        <span class="label-text font-semibold">Daily Laberi</span>
        <input type="number" step="0.01" min="0" name="{{ $field('daily_laberi') }}" value="{{ old("employees.{$index}.daily_laberi", $employee?->daily_laberi) }}" class="input input-bordered w-full" required>
    </label>

    <label class="form-control w-full">
        <span class="label-text font-semibold">Role</span>
        <select name="{{ $field('role') }}" class="select select-bordered w-full" required>
            @foreach (\App\Enums\EmployeeRole::cases() as $roleOption)
                <option value="{{ $roleOption->value }}" @selected(old("employees.{$index}.role", $employee?->role?->value) === $roleOption->value)>{{ $roleOption->label() }}</option>
            @endforeach
        </select>
    </label>

    <label class="form-control w-full">
        <span class="label-text font-semibold">Phone Number</span>
        <input type="text" name="{{ $field('phone_number') }}" value="{{ old("employees.{$index}.phone_number", $employee?->phone_number) }}" class="input input-bordered w-full" required maxlength="255">
    </label>

    <label class="form-control w-full">
        <span class="label-text font-semibold">Status</span>
        <select name="{{ $field('status') }}" class="select select-bordered w-full">
            @foreach (\App\Enums\EmployeeStatus::cases() as $statusOption)
                <option value="{{ $statusOption->value }}" @selected(old("employees.{$index}.status", $employee?->status?->value ?? 'active') === $statusOption->value)>{{ $statusOption->label() }}</option>
            @endforeach
        </select>
    </label>

    <label class="form-control w-full">
        <span class="label-text font-semibold">Joining Date</span>
        <input type="date" name="{{ $field('joining_date') }}" value="{{ old("employees.{$index}.joining_date", $employee?->joining_date?->format('Y-m-d')) }}" class="input input-bordered w-full" required>
    </label>

    <label class="form-control w-full md:col-span-2">
        <span class="label-text font-semibold">Notes</span>
        <textarea name="{{ $field('notes') }}" class="textarea textarea-bordered w-full">{{ old("employees.{$index}.notes", $employee?->notes) }}</textarea>
    </label>
</div>

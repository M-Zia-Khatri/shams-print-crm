@props(['index' => 0, 'employees' => []])

<div class="bulk-payment-row grid grid-cols-1 md:grid-cols-4 gap-3 rounded-lg border border-base-300 bg-base-100/60 p-4">
    <label class="form-control w-full">
        <span class="label-text font-semibold text-sm">Employee</span>
        <select name="payments[{{ $index }}][employee_id]" class="select select-bordered select-sm w-full" required>
            <option value="" disabled selected>Select Employee</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
            @endforeach
        </select>
    </label>
    <label class="form-control w-full">
        <span class="label-text font-semibold text-sm">Amount</span>
        <input type="number" step="0.01" min="0.01" name="payments[{{ $index }}][amount]" class="input input-bordered input-sm w-full" required>
    </label>
    <label class="form-control w-full">
        <span class="label-text font-semibold text-sm">Type</span>
        <select name="payments[{{ $index }}][payment_type]" class="select select-bordered select-sm w-full" required>
            @foreach (\App\Enums\PaymentType::cases() as $type)
                <option value="{{ $type->value }}">{{ $type->label() }}</option>
            @endforeach
        </select>
    </label>
    <div class="flex items-end">
        <button type="button" class="btn btn-error btn-sm remove-bulk-row {{ $index === 0 ? 'hidden' : '' }}">Remove</button>
    </div>
</div>

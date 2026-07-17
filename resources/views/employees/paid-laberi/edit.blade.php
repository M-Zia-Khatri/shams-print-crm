<x-app-layout>
    <x-section-title title="Edit Payment" subtitle="Update payment for {{ $employee->name }}." />

    @if ($errors->any())
        <div class="alert alert-error shadow-sm mb-4">
            <span>Please review the highlighted fields and try again.</span>
        </div>
    @endif

    <x-dashboard-card title="{{ $employee->name }}" description="Update payment details below.">
        <form method="POST" action="{{ route('employees.paid-laberi.update', [$employee, $payment]) }}" class="w-full space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="form-control w-full">
                    <span class="label-text font-semibold">Amount</span>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $payment->amount) }}" class="input input-bordered w-full" required>
                </label>

                <label class="form-control w-full">
                    <span class="label-text font-semibold">Payment Date</span>
                    <input type="date" name="paid_date" value="{{ old('paid_date', $payment->paid_date->format('Y-m-d')) }}" class="input input-bordered w-full" required>
                </label>

                <label class="form-control w-full">
                    <span class="label-text font-semibold">Payment Type</span>
                    <select name="payment_type" class="select select-bordered w-full" required>
                        @foreach (\App\Enums\PaymentType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('payment_type', $payment->payment_type->value) === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="form-control w-full">
                    <span class="label-text font-semibold">Reference No</span>
                    <input type="text" name="reference_no" value="{{ old('reference_no', $payment->reference_no) }}" class="input input-bordered w-full">
                </label>

                <label class="form-control w-full md:col-span-2">
                    <span class="label-text font-semibold">Description</span>
                    <textarea name="description" class="textarea textarea-bordered w-full">{{ old('description', $payment->description) }}</textarea>
                </label>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('employees.paid-laberi.index', $employee) }}" class="btn btn-ghost">Cancel</a>
                <x-action-button type="submit" variant="primary">Update Payment</x-action-button>
            </div>
        </form>
    </x-dashboard-card>
</x-app-layout>

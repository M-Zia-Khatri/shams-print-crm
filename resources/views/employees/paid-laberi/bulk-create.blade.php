<x-app-layout>
    <x-section-title title="Bulk Payment" subtitle="Record payments for multiple employees on the same date." />

    @if ($errors->any())
        <div class="alert alert-error shadow-sm mb-4">
            <span>Please review the highlighted fields and try again.</span>
        </div>
    @endif

    <x-dashboard-card title="Bulk Payments" description="Add rows for each employee being paid.">
        <form method="POST" action="{{ route('employee-paid-laberi.bulk-store') }}" class="w-full space-y-4" id="bulk-payment-form">
            @csrf

            <label class="form-control w-full max-w-xs">
                <span class="label-text font-semibold">Payment Date</span>
                <input type="date" name="paid_date" value="{{ old('paid_date', now()->toDateString()) }}" class="input input-bordered w-full" required>
            </label>

            <div id="bulk-payment-rows" class="space-y-3">
                @include('employees.partials.bulk-payment-row', ['index' => 0, 'employees' => $employees])
            </div>

            <x-action-button type="button" variant="outline" id="add-bulk-row-button">Add Row</x-action-button>

            <template id="bulk-payment-row-template">
                @include('employees.partials.bulk-payment-row', ['index' => '__INDEX__', 'employees' => $employees])
            </template>

            <div class="flex justify-end gap-2">
                <a href="{{ route('employee-payroll.index') }}" class="btn btn-ghost">Cancel</a>
                <x-action-button type="submit" variant="primary">Save Payments</x-action-button>
            </div>
        </form>
    </x-dashboard-card>

    @vite('resources/js/employee-paid-laberi-bulk.js')
</x-app-layout>

<x-app-layout>
    <x-section-title title="Add Expense" subtitle="Create a new expense with itemized breakdown." />

    @if ($errors->any())
        <div class="alert alert-error shadow-sm mb-4">
            <span>Please review the highlighted fields and try again.</span>
        </div>
    @endif

    <x-dashboard-card title="New Expense" description="Add expense details and line items below.">
        <form method="POST" action="{{ route('expenses.store') }}" class="w-full space-y-4" id="expense-form">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="form-control w-full">
                    <span class="label-text font-semibold">Description</span>
                    <input type="text" name="description" value="{{ old('description') }}"
                        placeholder="E.g., Office supplies" class="input input-bordered w-full @error('description') input-error @enderror" required maxlength="255">
                    @error('description')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
                </label>

                <label class="form-control w-full">
                    <span class="label-text font-semibold">Expense Date</span>
                    <input type="date" name="expense_date" value="{{ old('expense_date', now()->format('Y-m-d')) }}"
                        class="input input-bordered w-full @error('expense_date') input-error @enderror" required>
                    @error('expense_date')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
                </label>
            </div>

            <div id="expense-items-container" class="space-y-3">
                <div class="expense-item grid grid-cols-1 md:grid-cols-5 gap-3 rounded-lg border border-base-300 bg-base-100/60 p-4">
                    <label class="form-control w-full">
                        <span class="label-text font-semibold text-sm">Item</span>
                        <input type="text" name="expense_items[0][description]" 
                            placeholder="Item description" class="input input-bordered input-sm w-full" required>
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-semibold text-sm">Qty</span>
                        <input type="number" name="expense_items[0][qty]" value="1"
                            class="input input-bordered input-sm w-full" min="1" required>
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-semibold text-sm">Unit Price</span>
                        <input type="number" name="expense_items[0][unit_price]" placeholder="0.00"
                            class="input input-bordered input-sm w-full" step="0.01" min="0" required>
                    </label>
                    <div class="flex items-end">
                        <div class="form-control w-full">
                            <span class="label-text font-semibold text-sm">Subtotal</span>
                            <input type="text" class="input input-bordered input-sm w-full bg-base-200" disabled>
                        </div>
                    </div>
                    <div class="flex items-end">
                        <button type="button" class="btn btn-error btn-sm remove-item hidden">Remove</button>
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="button" id="add-item-btn" class="btn btn-outline">Add Item</button>
            </div>

            <div class="divider"></div>

            <div class="stats shadow w-full">
                <div class="stat">
                    <div class="stat-title">Total Expense</div>
                    <div class="stat-value text-primary" id="total-display">0.00</div>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('expenses.index') }}" class="btn btn-ghost">Cancel</a>
                <x-action-button type="submit" variant="primary">Create Expense</x-action-button>
            </div>
        </form>
    </x-dashboard-card>

    @vite('resources/js/expenses-create.js')
</x-app-layout>
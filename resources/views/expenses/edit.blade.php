<x-app-layout>
    <x-section-title title="Edit Expense" subtitle="Update expense details and line items." />

    @if ($errors->any())
        <div class="alert alert-error shadow-sm mb-4">
            <span>Please review the highlighted fields and try again.</span>
        </div>
    @endif

    <x-dashboard-card title="Edit Expense" description="Modify expense details below.">
        <form method="POST" action="{{ route('expenses.update', $expense) }}" class="w-full space-y-4" id="expense-form">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="form-control w-full">
                    <span class="label-text font-semibold">Description</span>
                    <input type="text" name="description" value="{{ old('description', $expense->description) }}"
                        placeholder="E.g., Office supplies" class="input input-bordered w-full @error('description') input-error @enderror" required maxlength="255">
                    @error('description')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
                </label>

                <label class="form-control w-full">
                    <span class="label-text font-semibold">Expense Date</span>
                    <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}"
                        class="input input-bordered w-full @error('expense_date') input-error @enderror" required>
                    @error('expense_date')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
                </label>
            </div>

            <div id="expense-items-container" class="space-y-3">
                @php $index = 0; @endphp
                @foreach ($expense->expense_list as $item)
                <div class="expense-item grid grid-cols-1 md:grid-cols-5 gap-3 rounded-lg border border-base-300 bg-base-100/60 p-4">
                    <label class="form-control w-full">
                        <span class="label-text font-semibold text-sm">Item</span>
                        <input type="text" name="expense_items[{{ $index }}][description]" 
                            value="{{ $item['description'] }}" placeholder="Item description" class="input input-bordered input-sm w-full" required>
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-semibold text-sm">Qty</span>
                        <input type="number" name="expense_items[{{ $index }}][qty]" value="{{ $item['qty'] }}"
                            class="input input-bordered input-sm w-full" min="1" required>
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-semibold text-sm">Unit Price</span>
                        <input type="number" name="expense_items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] }}"
                            class="input input-bordered input-sm w-full" step="0.01" min="0" required>
                    </label>
                    <div class="flex items-end">
                        <div class="form-control w-full">
                            <span class="label-text font-semibold text-sm">Subtotal</span>
                            <input type="text" class="input input-bordered input-sm w-full bg-base-200" disabled>
                        </div>
                    </div>
                    <div class="flex items-end">
                        <button type="button" class="btn btn-error btn-sm remove-item {{ $index === 0 ? 'hidden' : '' }}">Remove</button>
                    </div>
                </div>
                @php $index++; @endphp
                @endforeach
            </div>

            <div class="flex gap-2">
                <button type="button" id="add-item-btn" class="btn btn-outline">Add Item</button>
            </div>

            <div class="divider"></div>

            <div class="stats shadow w-full">
                <div class="stat">
                    <div class="stat-title">Total Expense</div>
                    <div class="stat-value text-primary" id="total-display">{{ number_format((float) $expense->total_expense, 2) }}</div>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('expenses.index') }}" class="btn btn-ghost">Cancel</a>
                <x-action-button type="submit" variant="primary">Update Expense</x-action-button>
            </div>
        </form>
    </x-dashboard-card>

    @vite('resources/js/expenses-create.js')
</x-app-layout>
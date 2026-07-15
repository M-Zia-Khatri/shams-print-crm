<x-app-layout>
    <div class="flex gap-4 items-center mb-6">
        <x-section-title title="{{ $expense->description }}" subtitle="Expense details and breakdown." />
        <div class="flex gap-2">
            <form method="GET" action="{{ route('expenses.edit', $expense) }}">
                <x-action-button type="submit" variant="secondary">Edit</x-action-button>
            </form>
            <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                onsubmit="return confirm('Delete this expense?')">
                @csrf
                @method('DELETE')
                <x-action-button type="submit" variant="error">Delete</x-action-button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-dashboard-card title="Date" description="Expense date">
            <div class="text-lg font-semibold">{{ $expense->expense_date->format('M d, Y') }}</div>
        </x-dashboard-card>

        <x-dashboard-card title="Total Expense" description="Total amount">
            <div class="text-2xl font-bold text-primary">{{ number_format((float) $expense->total_expense, 2) }}</div>
        </x-dashboard-card>

        <x-dashboard-card title="Items" description="Number of items">
            <div class="text-lg font-semibold">{{ count($expense->expense_list) }}</div>
        </x-dashboard-card>
    </div>

    <x-dashboard-card title="Expense Items" description="Detailed breakdown of items.">
        <div class="w-full overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expense->expense_list as $item)
                        <tr>
                            <td>{{ $item['description'] }}</td>
                            <td>{{ $item['qty'] }}</td>
                            <td>{{ number_format((float) $item['unit_price'], 2) }}</td>
                            <td class="font-semibold">{{ number_format((float) $item['qty'] * $item['unit_price'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-dashboard-card>

    <div class="flex justify-start gap-2 mt-6">
        <a href="{{ route('expenses.index') }}" class="btn btn-ghost">Back to Expenses</a>
    </div>
</x-app-layout>

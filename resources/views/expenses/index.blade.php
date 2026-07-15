<x-app-layout>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <x-section-title title="Expenses"
            subtitle="Track and manage business expenses with itemized breakdown." />

        <form method="GET" action="{{ route('expenses.create') }}">
            <x-action-button type="submit" variant="primary">Add Expense</x-action-button>
        </form>
    </div>

    @if (session('status'))
        <div class="alert alert-success shadow-sm">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-dashboard-card title="Today's Expenses" description="Total expenses for today">
            <div class="text-2xl font-bold text-primary">{{ number_format((float) $todayExpenses, 2) }}</div>
        </x-dashboard-card>

        <x-dashboard-card title="This Month" description="Total expenses this month">
            <div class="text-2xl font-bold text-primary">{{ number_format((float) $monthExpenses, 2) }}</div>
        </x-dashboard-card>

        <x-dashboard-card title="Total Expenses" description="All time expenses">
            <div class="text-2xl font-bold text-primary">{{ number_format((float) $totalExpenses, 2) }}</div>
        </x-dashboard-card>
    </div>

    <x-dashboard-card title="Filters" description="Search and filter expenses">
        <form method="GET" action="{{ route('expenses.index') }}" class="grid w-full grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
            <label class="form-control w-full">
                <span class="label-text font-semibold">Description</span>
                <input type="search" name="search" value="{{ $filters['search'] }}"
                    placeholder="Search expenses" class="input input-bordered w-full">
            </label>

            <label class="form-control w-full">
                <span class="label-text font-semibold">Start Date</span>
                <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="input input-bordered w-full">
            </label>

            <label class="form-control w-full">
                <span class="label-text font-semibold">End Date</span>
                <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="input input-bordered w-full">
            </label>

            <label class="form-control w-full">
                <span class="label-text font-semibold">Min Expense</span>
                <input type="number" step="0.01" min="0" name="min_expense" value="{{ $filters['min_expense'] }}" class="input input-bordered w-full">
            </label>

            <label class="form-control w-full">
                <span class="label-text font-semibold">Max Expense</span>
                <input type="number" step="0.01" min="0" name="max_expense" value="{{ $filters['max_expense'] }}" class="input input-bordered w-full">
            </label>

            <div class="flex items-end gap-2 lg:col-span-5">
                <x-action-button type="submit" variant="primary">Apply Filters</x-action-button>
                <a href="{{ route('expenses.index') }}" class="btn btn-ghost">Reset</a>
            </div>
        </form>
    </x-dashboard-card>

    <x-dashboard-card title="Expenses" description="All recorded expenses">
        <div class="w-full overflow-x-auto">
            @if ($expenses->isNotEmpty())
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                <td class="font-semibold">{{ $expense->description }}</td>
                                <td>{{ count($expense->expense_list) }} item(s)</td>
                                <td class="font-bold text-primary">{{ number_format((float) $expense->total_expense, 2) }}</td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <form method="GET" action="{{ route('expenses.show', $expense) }}">
                                            <x-action-button type="submit" variant="secondary" size="sm">View</x-action-button>
                                        </form>
                                        <form method="GET" action="{{ route('expenses.edit', $expense) }}">
                                            <x-action-button type="submit" variant="secondary" size="sm"><x-feathericon-edit class="w-4 h-4" /></x-action-button>
                                        </form>
                                        <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-action-button type="submit" variant="error" size="sm"><x-heroicon-o-trash class="w-4 h-4" /></x-action-button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $expenses->links() }}
                </div>
            @else
                <x-empty-state title="No expenses found" message="No expenses match the current filters." />
            @endif
        </div>
    </x-dashboard-card>
</x-app-layout>
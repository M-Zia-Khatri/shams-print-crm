<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $startDate = $request->query('start_date', '');
        $endDate = $request->query('end_date', '');
        $minExpense = $request->query('min_expense', '');
        $maxExpense = $request->query('max_expense', '');

        $query = Expense::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('description', 'like', "%{$search}%");
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('expense_date', [$startDate, $endDate]);
            })
            ->when($minExpense !== '', function ($query) use ($minExpense) {
                $query->where('total_expense', '>=', (float) $minExpense);
            })
            ->when($maxExpense !== '', function ($query) use ($maxExpense) {
                $query->where('total_expense', '<=', (float) $maxExpense);
            });

        $expenses = $query->latest('expense_date')->paginate(15)->withQueryString();

        $todayExpenses = Expense::getTodayExpenses();
        $monthExpenses = Expense::getMonthExpenses();
        $totalExpenses = Expense::getTotalExpenses();

        return view('expenses.index', [
            'expenses' => $expenses,
            'todayExpenses' => $todayExpenses,
            'monthExpenses' => $monthExpenses,
            'totalExpenses' => $totalExpenses,
            'filters' => [
                'search' => $search,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'min_expense' => $minExpense,
                'max_expense' => $maxExpense,
            ],
        ]);
    }

    public function create(): View
    {
        return view('expenses.create');
    }

    public function store(ExpenseRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $expenseItems = $request->validatedExpenses();
        $totalExpense = $request->computedTotalExpense();

        Expense::create([
            'description' => $validated['description'],
            'expense_date' => $validated['expense_date'],
            'expense_list' => $expenseItems,
            'total_expense' => $totalExpense,
        ]);

        return to_route('expenses.index')->with('status', 'Expense created successfully.');
    }

    public function show(Expense $expense): View
    {
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense): View
    {
        return view('expenses.edit', compact('expense'));
    }

    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validated();
        $expenseItems = $request->validatedExpenses();
        $totalExpense = $request->computedTotalExpense();

        $expense->update([
            'description' => $validated['description'],
            'expense_date' => $validated['expense_date'],
            'expense_list' => $expenseItems,
            'total_expense' => $totalExpense,
        ]);

        return to_route('expenses.index')->with('status', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();
        return to_route('expenses.index')->with('status', 'Expense deleted successfully.');
    }
}
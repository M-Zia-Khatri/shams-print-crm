<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'description',
    'expense_date',
    'expense_list',
    'total_expense',
])]
class Expense extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'expense_list' => 'array',
            'total_expense' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public static function getExpensesSummary($startDate = null, $endDate = null): array
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->whereBetween('expense_date', [$startDate, $endDate]);
        }

        return [
            'total' => $query->sum('total_expense'),
            'count' => $query->count(),
        ];
    }

    public static function getTodayExpenses(): float
    {
        return self::whereDate('expense_date', today())->sum('total_expense');
    }

    public static function getMonthExpenses(): float
    {
        return self::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('total_expense');
    }

    public static function getTotalExpenses(): float
    {
        return self::sum('total_expense');
    }
}
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return [
                'description' => ['required', 'string', 'max:255'],
                'expense_date' => ['required', 'date'],
                'expense_items' => ['required', 'array', 'min:1'],
                'expense_items.*.description' => ['required', 'string', 'max:255'],
                'expense_items.*.qty' => ['required', 'integer', 'min:1'],
                'expense_items.*.unit_price' => ['required', 'numeric', 'min:0'],
            ];
        }

        return [
            'description' => ['required', 'string', 'max:255'],
            'expense_date' => ['required', 'date'],
            'expense_items' => ['required', 'array', 'min:1'],
            'expense_items.*.description' => ['required', 'string', 'max:255'],
            'expense_items.*.qty' => ['required', 'integer', 'min:1'],
            'expense_items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function validatedExpenses(): array
    {
        return $this->validated('expense_items', []);
    }

    public function computedTotalExpense(): string
    {
        $total = 0;
        foreach ($this->validatedExpenses() as $item) {
            $total += $item['qty'] * $item['unit_price'];
        }
        return number_format($total, 2, '.', '');
    }
}
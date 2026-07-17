<?php

namespace App\Http\Requests;

use App\Enums\DailyShift;
use App\Models\PayrollLock;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shift_date' => ['required', 'date'],
            'default_shift' => ['required', Rule::enum(DailyShift::class)],
            'employee_ids' => ['nullable', 'array'],
            'employee_ids.*' => ['integer', 'exists:employees,id'],
            'overrides' => ['nullable', 'array'],
            'overrides.*' => [Rule::enum(DailyShift::class)],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->filled('shift_date') && PayrollLock::isDateLocked($this->input('shift_date'))) {
                    $validator->errors()->add('shift_date', 'This date falls inside a locked payroll week.');
                }
            },
        ];
    }
}

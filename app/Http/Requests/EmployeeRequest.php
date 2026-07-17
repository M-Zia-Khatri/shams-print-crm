<?php

namespace App\Http\Requests;

use App\Enums\EmployeeRole;
use App\Enums\EmployeeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->has('employees')) {
            return [
                'employees' => ['required', 'array', 'min:1'],
                'employees.*.name' => ['required', 'string', 'max:255'],
                'employees.*.daily_laberi' => ['required', 'numeric', 'min:0'],
                'employees.*.role' => ['required', Rule::enum(EmployeeRole::class)],
                'employees.*.phone_number' => ['required', 'string', 'max:255'],
                'employees.*.status' => ['nullable', Rule::enum(EmployeeStatus::class)],
                'employees.*.joining_date' => ['required', 'date'],
                'employees.*.notes' => ['nullable', 'string'],
            ];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'daily_laberi' => ['required', 'numeric', 'min:0'],
            'role' => ['required', Rule::enum(EmployeeRole::class)],
            'phone_number' => ['required', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(EmployeeStatus::class)],
            'joining_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function validatedEmployees(): array
    {
        return $this->validated('employees', []);
    }
}

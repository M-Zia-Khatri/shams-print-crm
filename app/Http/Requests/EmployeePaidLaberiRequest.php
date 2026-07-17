<?php

namespace App\Http\Requests;

use App\Enums\PaymentType;
use App\Models\PayrollLock;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeePaidLaberiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('payments') && $this->route('employee') !== null) {
            $this->merge([
                'employee_id' => $this->route('employee')->id,
            ]);
        }
    }

    public function rules(): array
    {
        if ($this->has('payments')) {
            return [
                'paid_date' => ['required', 'date'],
                'payments' => ['required', 'array', 'min:1'],
                'payments.*.employee_id' => ['required', 'integer', 'exists:employees,id'],
                'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
                'payments.*.payment_type' => ['required', Rule::enum(PaymentType::class)],
                'payments.*.reference_no' => ['nullable', 'string', 'max:255'],
                'payments.*.description' => ['nullable', 'string'],
            ];
        }

        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_date' => ['required', 'date'],
            'payment_type' => ['required', Rule::enum(PaymentType::class)],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $date = $this->input('paid_date');

                if ($date && PayrollLock::isDateLocked($date)) {
                    $validator->errors()->add('paid_date', 'This date falls inside a locked payroll week.');
                }
            },
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function validatedPayments(): array
    {
        return $this->validated('payments', []);
    }

    /**
     * @return array<string, mixed>
     */
    public function paymentAttributes(): array
    {
        return $this->safe()->only([
            'amount',
            'paid_date',
            'payment_type',
            'reference_no',
            'description',
        ]);
    }
}

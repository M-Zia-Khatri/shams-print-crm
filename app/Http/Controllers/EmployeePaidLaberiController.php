<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeePaidLaberiRequest;
use App\Models\Employee;
use App\Models\EmployeePaidLaberi;
use App\Models\PayrollLock;
use App\Services\PayrollCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeePaidLaberiController extends Controller
{
    public function __construct(private PayrollCalculationService $payrollCalculationService) {}

    public function index(Request $request, Employee $employee): View
    {
        $resolved = $this->payrollCalculationService->resolveRangeFromRequest($request);
        $start = $resolved['start'];
        $end = $resolved['end'];

        $payments = $employee->paidLaberi()
            ->betweenDates($start, $end)
            ->latest('paid_date')
            ->paginate(15)
            ->withQueryString();

        return view('employees.paid-laberi.index', [
            'employee' => $employee,
            'payments' => $payments,
            'filters' => [
                'start_date' => $start,
                'end_date' => $end,
                'range' => $resolved['range'],
            ],
        ]);
    }

    public function create(Employee $employee): View
    {
        return view('employees.paid-laberi.create', ['employee' => $employee]);
    }

    public function store(EmployeePaidLaberiRequest $request, Employee $employee): RedirectResponse
    {
        $employee->paidLaberi()->create($request->paymentAttributes());

        return to_route('employees.paid-laberi.index', $employee)->with('status', 'Payment recorded.');
    }

    public function edit(Employee $employee, EmployeePaidLaberi $payment): View
    {
        $this->ensurePaymentBelongsToEmployee($employee, $payment);

        return view('employees.paid-laberi.edit', compact('employee', 'payment'));
    }

    public function update(EmployeePaidLaberiRequest $request, Employee $employee, EmployeePaidLaberi $payment): RedirectResponse
    {
        $this->ensurePaymentBelongsToEmployee($employee, $payment);

        $payment->update($request->paymentAttributes());

        return to_route('employees.paid-laberi.index', $employee)->with('status', 'Payment updated.');
    }

    public function destroy(Employee $employee, EmployeePaidLaberi $payment): RedirectResponse
    {
        $this->ensurePaymentBelongsToEmployee($employee, $payment);

        if (PayrollLock::isDateLocked($payment->paid_date)) {
            return back()->withErrors(['paid_date' => 'This payment falls inside a locked payroll week.']);
        }

        $payment->delete();

        return to_route('employees.paid-laberi.index', $employee)->with('status', 'Payment deleted.');
    }

    public function bulkCreate(): View
    {
        $employees = Employee::active()->orderBy('name')->get();

        return view('employees.paid-laberi.bulk-create', compact('employees'));
    }

    public function bulkStore(EmployeePaidLaberiRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $paidDate = $validated['paid_date'];

        DB::transaction(function () use ($request, $paidDate): void {
            foreach ($request->validatedPayments() as $payment) {
                EmployeePaidLaberi::create(array_merge($payment, ['paid_date' => $paidDate]));
            }
        });

        return to_route('employee-payroll.index')->with('status', 'Payments recorded.');
    }

    private function ensurePaymentBelongsToEmployee(Employee $employee, EmployeePaidLaberi $payment): void
    {
        abort_unless($payment->employee_id === $employee->id, 404);
    }
}

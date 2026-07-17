<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeePaidLaberiRequest;
use App\Models\Employee;
use App\Models\EmployeePaidLaberi;
use App\Models\PayrollLock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeePaidLaberiController extends Controller
{
    public function index(Request $request, Employee $employee): View
    {
        [$start, $end] = $this->resolveDateRange($request);

        $payments = $employee->paidLaberi()
            ->betweenDates($start, $end)
            ->latest('paid_date')
            ->paginate(15)
            ->withQueryString();

        return view('employees.paid-laberi.index', [
            'employee' => $employee,
            'payments' => $payments,
            'filters' => ['start_date' => $start, 'end_date' => $end],
        ]);
    }

    public function create(Employee $employee): View
    {
        return view('employees.paid-laberi.create', ['employee' => $employee]);
    }

    public function store(EmployeePaidLaberiRequest $request, Employee $employee): RedirectResponse
    {
        $employee->paidLaberi()->create($request->validated());

        return to_route('employees.paid-laberi.index', $employee)->with('status', 'Payment recorded.');
    }

    public function edit(Employee $employee, EmployeePaidLaberi $payment): View
    {
        return view('employees.paid-laberi.edit', compact('employee', 'payment'));
    }

    public function update(EmployeePaidLaberiRequest $request, Employee $employee, EmployeePaidLaberi $payment): RedirectResponse
    {
        $payment->update($request->validated());

        return to_route('employees.paid-laberi.index', $employee)->with('status', 'Payment updated.');
    }

    public function destroy(Employee $employee, EmployeePaidLaberi $payment): RedirectResponse
    {
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

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveDateRange(Request $request): array
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        if (is_string($start) && $start !== '' && is_string($end) && $end !== '') {
            return [$start, $end];
        }

        return [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()];
    }
}

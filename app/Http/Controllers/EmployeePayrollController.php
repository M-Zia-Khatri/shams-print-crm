<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollLock;
use App\Services\PayrollCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeePayrollController extends Controller
{
    public function __construct(private PayrollCalculationService $payrollCalculationService) {}

    public function index(Request $request): View
    {
        $resolved = $this->payrollCalculationService->resolveRangeFromRequest($request);
        $start = $resolved['start'];
        $end = $resolved['end'];

        $employees = Employee::active()
            ->with(['dailyLaberiEntries', 'paidLaberi'])
            ->orderBy('name')
            ->get();

        $rows = $employees->map(function (Employee $employee) use ($start, $end) {
            $summary = $this->payrollCalculationService->summaryForEmployee($employee, $start, $end);

            return array_merge(['employee' => $employee], $summary);
        });

        $exactLock = PayrollLock::findForWeek($start, $end);
        $lock = $exactLock ?? PayrollLock::findOverlapping($start, $end);

        return view('employee-payroll.index', [
            'rows' => $rows,
            'lock' => $lock,
            'exactLock' => $exactLock,
            'payrollType' => $resolved['payroll_type'],
            'filters' => [
                'start_date' => $start,
                'end_date' => $end,
                'range' => $resolved['range'],
            ],
        ]);
    }

    public function lock(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'week_start_date' => ['required', 'date'],
            'week_end_date' => ['required', 'date', 'after_or_equal:week_start_date'],
        ]);

        PayrollLock::firstOrCreate(
            [
                'week_start_date' => $validated['week_start_date'],
                'week_end_date' => $validated['week_end_date'],
            ],
            [
                'locked_by' => $request->user()->id,
                'locked_at' => now(),
            ],
        );

        return back()->with('status', 'Payroll week locked.');
    }

    public function unlock(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'week_start_date' => ['required', 'date'],
            'week_end_date' => ['required', 'date', 'after_or_equal:week_start_date'],
        ]);

        PayrollLock::findForWeek($validated['week_start_date'], $validated['week_end_date'])?->delete();

        return back()->with('status', 'Payroll week unlocked.');
    }
}

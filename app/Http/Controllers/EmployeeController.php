<?php

namespace App\Http\Controllers;

use App\Enums\DailyShift;
use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Models\EmployeeDailyLaberiEntry;
use App\Services\PayrollCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(private PayrollCalculationService $payrollCalculationService) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $role = (string) $request->query('role', '');
        $status = (string) $request->query('status', '');

        $employees = Employee::query()
            ->with(['dailyLaberiEntries', 'paidLaberi'])
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($role !== '', fn ($query) => $query->where('role', $role))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $lifetimeStart = '1970-01-01';
        $lifetimeEnd = '2099-12-31';

        $employees->getCollection()->transform(function (Employee $employee) use ($lifetimeStart, $lifetimeEnd) {
            $summary = $this->payrollCalculationService->summaryForEmployee($employee, $lifetimeStart, $lifetimeEnd);
            $employee->setAttribute('lifetime_earned', $summary['total_earned']);
            $employee->setAttribute('lifetime_advance', $summary['total_advance']);
            $employee->setAttribute('lifetime_bonus', $summary['total_bonus']);
            $employee->setAttribute('lifetime_paid', $summary['total_paid']);
            $employee->setAttribute('lifetime_remaining', $summary['remaining']);

            return $employee;
        });

        $today = now()->toDateString();
        $activeEmployees = Employee::active()->get();
        $activeEmployeeIds = $activeEmployees->pluck('id');
        $todayEntries = EmployeeDailyLaberiEntry::whereIn('employee_id', $activeEmployeeIds)
            ->whereDate('laberi_date', $today)
            ->get();

        $workingToday = $todayEntries->filter(fn ($entry) => $entry->daily_shift->isWorkingDay())->count();
        $leaveToday = $todayEntries->filter(fn ($entry) => $entry->daily_shift === DailyShift::Leave)->count();

        $currentWeek = $this->payrollCalculationService->currentWeekRange();

        return view('employees.index', [
            'employees' => $employees,
            'filters' => [
                'search' => $search,
                'role' => $role,
                'status' => $status,
            ],
            'widgets' => [
                'total_employees' => Employee::count(),
                'working_today' => $workingToday,
                'leave_today' => $leaveToday,
                'weekly_payroll_amount' => $this->payrollCalculationService->weeklyPayrollAmount(
                    $activeEmployees->loadMissing(['dailyLaberiEntries', 'paidLaberi']),
                    $currentWeek['start']->toDateString(),
                    $currentWeek['end']->toDateString(),
                ),
                'pending_weekly_payment' => $this->payrollCalculationService->pendingWeeklyPaymentAmount(
                    $activeEmployees,
                    $currentWeek['start']->toDateString(),
                    $currentWeek['end']->toDateString(),
                ),
            ],
        ]);
    }

    public function create(): View
    {
        return view('employees.create');
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            if ($request->has('employees')) {
                foreach ($request->validatedEmployees() as $employeeData) {
                    Employee::create($employeeData);
                }
            } else {
                Employee::create($request->validated());
            }
        });

        return to_route('employees.index')->with('status', 'Employee(s) created successfully.');
    }

    public function show(Request $request, Employee $employee): View
    {
        $employee->loadMissing(['dailyLaberiEntries', 'paidLaberi']);
        $currentWeek = $this->payrollCalculationService->currentWeekRange();
        $lifetimeStart = '1970-01-01';
        $lifetimeEnd = '2099-12-31';

        $currentWeekSummary = $this->payrollCalculationService->summaryForEmployee(
            $employee,
            $currentWeek['start']->toDateString(),
            $currentWeek['end']->toDateString(),
        );

        $lifetimeSummary = $this->payrollCalculationService->summaryForEmployee($employee, $lifetimeStart, $lifetimeEnd);

        $shiftBreakdown = $this->payrollCalculationService->shiftBreakdown(
            $employee,
            $currentWeek['start']->toDateString(),
            $currentWeek['end']->toDateString(),
        );

        [$timelineStart, $timelineEnd] = $this->resolveDateRange($request);

        $shiftEntries = $employee->dailyLaberiEntries()
            ->betweenDates($timelineStart, $timelineEnd)
            ->get()
            ->map(fn ($entry) => [
                'type' => 'shift',
                'date' => $entry->laberi_date,
                'label' => $entry->daily_shift->label(),
            ]);

        $paymentEntries = $employee->paidLaberi()
            ->betweenDates($timelineStart, $timelineEnd)
            ->get()
            ->map(fn ($payment) => [
                'type' => 'payment',
                'date' => $payment->paid_date,
                'label' => $payment->payment_type->label().' — '.number_format((float) $payment->amount, 2),
            ]);

        $timeline = $shiftEntries->concat($paymentEntries)->sortByDesc('date')->values();

        return view('employees.show', [
            'employee' => $employee,
            'currentWeekSummary' => $currentWeekSummary,
            'lifetimeSummary' => $lifetimeSummary,
            'shiftBreakdown' => $shiftBreakdown,
            'timeline' => $timeline,
            'recentShifts' => $employee->dailyLaberiEntries()->latest('laberi_date')->take(10)->get(),
            'recentPayments' => $employee->paidLaberi()->latest('paid_date')->take(10)->get(),
            'filters' => [
                'start_date' => $timelineStart,
                'end_date' => $timelineEnd,
            ],
        ]);
    }

    public function edit(Employee $employee): View
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee->update($request->validated());

        return to_route('employees.index')->with('status', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return to_route('employees.index')->with('status', 'Employee deleted successfully.');
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

        $currentWeek = $this->payrollCalculationService->currentWeekRange();

        return [$currentWeek['start']->toDateString(), $currentWeek['end']->toDateString()];
    }
}

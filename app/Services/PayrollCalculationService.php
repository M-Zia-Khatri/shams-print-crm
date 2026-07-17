<?php

namespace App\Services;

use App\Enums\DailyShift;
use App\Enums\PaymentType;
use App\Enums\PayrollPeriodType;
use App\Models\Employee;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PayrollCalculationService
{
    /**
     * @return array{start: CarbonImmutable, end: CarbonImmutable}
     */
    public function todayRange(): array
    {
        $today = CarbonImmutable::now()->startOfDay();

        return [
            'start' => $today,
            'end' => $today->endOfDay(),
        ];
    }

    /**
     * @return array{start: CarbonImmutable, end: CarbonImmutable}
     */
    public function currentWeekRange(): array
    {
        $now = CarbonImmutable::now();

        return [
            'start' => $now->startOfWeek(),
            'end' => $now->endOfWeek(),
        ];
    }

    /**
     * @return array{start: CarbonImmutable, end: CarbonImmutable}
     */
    public function lastWeekRange(): array
    {
        $lastWeek = CarbonImmutable::now()->subWeek();

        return [
            'start' => $lastWeek->startOfWeek(),
            'end' => $lastWeek->endOfWeek(),
        ];
    }

    /**
     * @return array{start: CarbonImmutable, end: CarbonImmutable}
     */
    public function currentMonthRange(): array
    {
        $now = CarbonImmutable::now();

        return [
            'start' => $now->startOfMonth(),
            'end' => $now->endOfMonth(),
        ];
    }

    /**
     * @return array{start: string, end: string, payroll_type: PayrollPeriodType, range: string}
     */
    public function resolveRangeFromRequest(Request $request): array
    {
        $mode = (string) $request->query('range', 'current');
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        if ($mode === 'custom' && is_string($start) && $start !== '' && is_string($end) && $end !== '') {
            return [
                'start' => CarbonImmutable::parse($start)->toDateString(),
                'end' => CarbonImmutable::parse($end)->toDateString(),
                'payroll_type' => PayrollPeriodType::Custom,
                'range' => 'custom',
            ];
        }

        $resolved = match ($mode) {
            'today' => [...$this->todayRange(), 'payroll_type' => PayrollPeriodType::Custom, 'range' => 'today'],
            'last' => [...$this->lastWeekRange(), 'payroll_type' => PayrollPeriodType::Weekly, 'range' => 'last'],
            'month' => [...$this->currentMonthRange(), 'payroll_type' => PayrollPeriodType::Monthly, 'range' => 'month'],
            default => [...$this->currentWeekRange(), 'payroll_type' => PayrollPeriodType::Weekly, 'range' => 'current'],
        };

        return [
            'start' => $resolved['start']->toDateString(),
            'end' => $resolved['end']->toDateString(),
            'payroll_type' => $resolved['payroll_type'],
            'range' => $resolved['range'],
        ];
    }

    public function totalShiftPercentage(Employee $employee, string $start, string $end): float
    {
        return (float) $this->entriesInRange($employee, $start, $end)
            ->sum(fn ($entry) => $entry->daily_shift->percentage());
    }

    public function workingDays(Employee $employee, string $start, string $end): int
    {
        return $this->entriesInRange($employee, $start, $end)
            ->filter(fn ($entry) => $entry->daily_shift->isWorkingDay())
            ->count();
    }

    public function totalEarned(Employee $employee, string $start, string $end): float
    {
        return round((float) $employee->daily_laberi * $this->totalShiftPercentage($employee, $start, $end), 2);
    }

    public function totalByPaymentType(Employee $employee, string $start, string $end, PaymentType $type): float
    {
        return round((float) $this->paymentsInRange($employee, $start, $end)
            ->filter(fn ($payment) => $payment->payment_type === $type)
            ->sum('amount'), 2);
    }

    public function totalAdvance(Employee $employee, string $start, string $end): float
    {
        return $this->totalByPaymentType($employee, $start, $end, PaymentType::Advance);
    }

    public function totalBonus(Employee $employee, string $start, string $end): float
    {
        return $this->totalByPaymentType($employee, $start, $end, PaymentType::Bonus);
    }

    public function totalPaid(Employee $employee, string $start, string $end): float
    {
        return $this->totalByPaymentType($employee, $start, $end, PaymentType::Salary);
    }

    public function totalDeduction(Employee $employee, string $start, string $end): float
    {
        return $this->totalByPaymentType($employee, $start, $end, PaymentType::Deduction);
    }

    public function remainingAmount(Employee $employee, string $start, string $end): float
    {
        return round(
            $this->totalEarned($employee, $start, $end)
            + $this->totalBonus($employee, $start, $end)
            - $this->totalAdvance($employee, $start, $end)
            - $this->totalPaid($employee, $start, $end)
            - $this->totalDeduction($employee, $start, $end),
            2,
        );
    }

    /**
     * @return array{
     *     working_days: int,
     *     total_shift_percentage: float,
     *     total_earned: float,
     *     total_advance: float,
     *     total_bonus: float,
     *     total_paid: float,
     *     total_deduction: float,
     *     remaining: float
     * }
     */
    public function summaryForEmployee(Employee $employee, string $start, string $end): array
    {
        $totalShiftPercentage = $this->totalShiftPercentage($employee, $start, $end);
        $totalEarned = round((float) $employee->daily_laberi * $totalShiftPercentage, 2);
        $totalAdvance = $this->totalAdvance($employee, $start, $end);
        $totalBonus = $this->totalBonus($employee, $start, $end);
        $totalPaid = $this->totalPaid($employee, $start, $end);
        $totalDeduction = $this->totalDeduction($employee, $start, $end);

        return [
            'working_days' => $this->workingDays($employee, $start, $end),
            'total_shift_percentage' => $totalShiftPercentage,
            'total_earned' => $totalEarned,
            'total_advance' => $totalAdvance,
            'total_bonus' => $totalBonus,
            'total_paid' => $totalPaid,
            'total_deduction' => $totalDeduction,
            'remaining' => round($totalEarned + $totalBonus - $totalAdvance - $totalPaid - $totalDeduction, 2),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function shiftBreakdown(Employee $employee, string $start, string $end): array
    {
        $entries = $this->entriesInRange($employee, $start, $end);

        $breakdown = [];
        foreach (DailyShift::cases() as $shift) {
            $breakdown[$shift->value] = $entries->filter(fn ($entry) => $entry->daily_shift === $shift)->count();
        }

        return $breakdown;
    }

    public function weeklyPayrollAmount(Collection $employees, string $start, string $end): float
    {
        return round((float) $employees->sum(fn (Employee $employee) => $this->totalEarned($employee, $start, $end)), 2);
    }

    public function pendingWeeklyPaymentAmount(Collection $employees, string $start, string $end): float
    {
        return round((float) $employees->sum(fn (Employee $employee) => $this->remainingAmount($employee, $start, $end)), 2);
    }

    /**
     * @return Collection<int, mixed>
     */
    private function entriesInRange(Employee $employee, string $start, string $end): Collection
    {
        $startDate = CarbonImmutable::parse($start)->startOfDay();
        $endDate = CarbonImmutable::parse($end)->startOfDay();

        return $employee->dailyLaberiEntries
            ->where('laberi_date', '>=', $startDate)
            ->where('laberi_date', '<=', $endDate);
    }

    /**
     * @return Collection<int, mixed>
     */
    private function paymentsInRange(Employee $employee, string $start, string $end): Collection
    {
        $startDate = CarbonImmutable::parse($start)->startOfDay();
        $endDate = CarbonImmutable::parse($end)->startOfDay();

        return $employee->paidLaberi
            ->where('paid_date', '>=', $startDate)
            ->where('paid_date', '<=', $endDate);
    }
}

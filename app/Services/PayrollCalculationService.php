<?php

namespace App\Services;

use App\Enums\DailyShift;
use App\Models\Employee;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class PayrollCalculationService
{
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

    public function totalShiftPercentage(Employee $employee, string $start, string $end): float
    {
        return (float) $employee->dailyLaberiEntries()
            ->betweenDates($start, $end)
            ->get()
            ->sum(fn ($entry) => $entry->daily_shift->percentage());
    }

    public function workingDays(Employee $employee, string $start, string $end): int
    {
        return $employee->dailyLaberiEntries()
            ->betweenDates($start, $end)
            ->get()
            ->filter(fn ($entry) => $entry->daily_shift->isWorkingDay())
            ->count();
    }

    public function totalEarned(Employee $employee, string $start, string $end): float
    {
        return (float) $employee->daily_laberi * $this->totalShiftPercentage($employee, $start, $end);
    }

    public function totalPaid(Employee $employee, string $start, string $end): float
    {
        return (float) $employee->paidLaberi()
            ->betweenDates($start, $end)
            ->sum('amount');
    }

    public function remainingAmount(Employee $employee, string $start, string $end): float
    {
        return $this->totalEarned($employee, $start, $end) - $this->totalPaid($employee, $start, $end);
    }

    /**
     * @return array{working_days: int, total_shift_percentage: float, total_earned: float, total_paid: float, remaining: float}
     */
    public function summaryForEmployee(Employee $employee, string $start, string $end): array
    {
        $totalShiftPercentage = $this->totalShiftPercentage($employee, $start, $end);
        $totalEarned = (float) $employee->daily_laberi * $totalShiftPercentage;
        $totalPaid = $this->totalPaid($employee, $start, $end);

        return [
            'working_days' => $this->workingDays($employee, $start, $end),
            'total_shift_percentage' => $totalShiftPercentage,
            'total_earned' => $totalEarned,
            'total_paid' => $totalPaid,
            'remaining' => $totalEarned - $totalPaid,
        ];
    }

    /**
     * @return array<string, int>
     */
    public function shiftBreakdown(Employee $employee, string $start, string $end): array
    {
        $entries = $employee->dailyLaberiEntries()->betweenDates($start, $end)->get();

        $breakdown = [];
        foreach (DailyShift::cases() as $shift) {
            $breakdown[$shift->value] = $entries->filter(fn ($entry) => $entry->daily_shift === $shift)->count();
        }

        return $breakdown;
    }

    public function weeklyPayrollAmount(Collection $employees, string $start, string $end): float
    {
        return (float) $employees->sum(fn (Employee $employee) => $this->totalEarned($employee, $start, $end));
    }

    public function pendingWeeklyPaymentAmount(Collection $employees, string $start, string $end): float
    {
        return (float) $employees->sum(fn (Employee $employee) => $this->remainingAmount($employee, $start, $end));
    }
}

<?php

use App\Enums\DailyShift;
use App\Enums\PaymentType;
use App\Enums\PayrollPeriodType;
use App\Models\Employee;
use App\Models\EmployeeDailyLaberiEntry;
use App\Models\EmployeePaidLaberi;
use App\Services\PayrollCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->payroll = new PayrollCalculationService;
});

it('calculates remaining as earned plus bonus minus advance paid and deduction', function () {
    $employee = Employee::factory()->create(['daily_laberi' => 750]);

    EmployeeDailyLaberiEntry::factory()->create([
        'employee_id' => $employee->id,
        'laberi_date' => '2026-07-13',
        'daily_shift' => DailyShift::Full,
    ]);
    EmployeeDailyLaberiEntry::factory()->create([
        'employee_id' => $employee->id,
        'laberi_date' => '2026-07-14',
        'daily_shift' => DailyShift::Full,
    ]);
    EmployeeDailyLaberiEntry::factory()->create([
        'employee_id' => $employee->id,
        'laberi_date' => '2026-07-15',
        'daily_shift' => DailyShift::Full,
    ]);
    EmployeeDailyLaberiEntry::factory()->create([
        'employee_id' => $employee->id,
        'laberi_date' => '2026-07-16',
        'daily_shift' => DailyShift::Full,
    ]);
    EmployeeDailyLaberiEntry::factory()->create([
        'employee_id' => $employee->id,
        'laberi_date' => '2026-07-17',
        'daily_shift' => DailyShift::Full,
    ]);
    EmployeeDailyLaberiEntry::factory()->create([
        'employee_id' => $employee->id,
        'laberi_date' => '2026-07-18',
        'daily_shift' => DailyShift::Full,
    ]);

    EmployeePaidLaberi::factory()->create([
        'employee_id' => $employee->id,
        'amount' => 1000,
        'paid_date' => '2026-07-14',
        'payment_type' => PaymentType::Advance,
    ]);
    EmployeePaidLaberi::factory()->create([
        'employee_id' => $employee->id,
        'amount' => 500,
        'paid_date' => '2026-07-15',
        'payment_type' => PaymentType::Salary,
    ]);
    EmployeePaidLaberi::factory()->create([
        'employee_id' => $employee->id,
        'amount' => 200,
        'paid_date' => '2026-07-16',
        'payment_type' => PaymentType::Bonus,
    ]);

    $employee->load(['dailyLaberiEntries', 'paidLaberi']);

    $summary = $this->payroll->summaryForEmployee($employee, '2026-07-13', '2026-07-19');

    expect($summary['total_earned'])->toBe(4500.0)
        ->and($summary['total_advance'])->toBe(1000.0)
        ->and($summary['total_bonus'])->toBe(200.0)
        ->and($summary['total_paid'])->toBe(500.0)
        ->and($summary['remaining'])->toBe(3200.0);
});

it('treats bonus as a credit and salary as paid', function () {
    $employee = Employee::factory()->create(['daily_laberi' => 1000]);

    EmployeeDailyLaberiEntry::factory()->create([
        'employee_id' => $employee->id,
        'laberi_date' => '2026-07-13',
        'daily_shift' => DailyShift::Full,
    ]);

    EmployeePaidLaberi::factory()->create([
        'employee_id' => $employee->id,
        'amount' => 100,
        'paid_date' => '2026-07-13',
        'payment_type' => PaymentType::Bonus,
    ]);

    $employee->load(['dailyLaberiEntries', 'paidLaberi']);

    expect($this->payroll->remainingAmount($employee, '2026-07-13', '2026-07-13'))->toBe(1100.0);
});

it('returns zero totals when there are no shifts or payments', function () {
    $employee = Employee::factory()->create(['daily_laberi' => 750]);
    $employee->load(['dailyLaberiEntries', 'paidLaberi']);

    $summary = $this->payroll->summaryForEmployee($employee, '2026-07-13', '2026-07-19');

    expect($summary['total_earned'])->toBe(0.0)
        ->and($summary['total_advance'])->toBe(0.0)
        ->and($summary['total_bonus'])->toBe(0.0)
        ->and($summary['total_paid'])->toBe(0.0)
        ->and($summary['remaining'])->toBe(0.0);
});

it('allows negative remaining on overpayment', function () {
    $employee = Employee::factory()->create(['daily_laberi' => 500]);

    EmployeeDailyLaberiEntry::factory()->create([
        'employee_id' => $employee->id,
        'laberi_date' => '2026-07-13',
        'daily_shift' => DailyShift::Full,
    ]);

    EmployeePaidLaberi::factory()->create([
        'employee_id' => $employee->id,
        'amount' => 800,
        'paid_date' => '2026-07-13',
        'payment_type' => PaymentType::Salary,
    ]);

    $employee->load(['dailyLaberiEntries', 'paidLaberi']);

    expect($this->payroll->remainingAmount($employee, '2026-07-13', '2026-07-13'))->toBe(-300.0);
});

it('resolves today month week and custom ranges with payroll types', function () {
    $today = $this->payroll->resolveRangeFromRequest(Request::create('/employee-payroll', 'GET', ['range' => 'today']));
    $week = $this->payroll->resolveRangeFromRequest(Request::create('/employee-payroll', 'GET', ['range' => 'current']));
    $month = $this->payroll->resolveRangeFromRequest(Request::create('/employee-payroll', 'GET', ['range' => 'month']));
    $custom = $this->payroll->resolveRangeFromRequest(Request::create('/employee-payroll', 'GET', [
        'range' => 'custom',
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-10',
    ]));

    expect($today['payroll_type'])->toBe(PayrollPeriodType::Custom)
        ->and($week['payroll_type'])->toBe(PayrollPeriodType::Weekly)
        ->and($month['payroll_type'])->toBe(PayrollPeriodType::Monthly)
        ->and($custom['payroll_type'])->toBe(PayrollPeriodType::Custom)
        ->and($custom['start'])->toBe('2026-07-01')
        ->and($custom['end'])->toBe('2026-07-10');
});

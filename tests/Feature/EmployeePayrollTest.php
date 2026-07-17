<?php

use App\Enums\DailyShift;
use App\Enums\PaymentType;
use App\Models\Employee;
use App\Models\EmployeeDailyLaberiEntry;
use App\Models\PayrollLock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function adminUser(): User
{
    return User::factory()->create([
        'name' => 'admin-'.uniqid(),
        'role' => 'admin',
    ]);
}

it('shows advance bonus and payroll type on the payroll page', function () {
    $user = adminUser();
    $employee = Employee::factory()->create(['name' => 'Shahbaz', 'daily_laberi' => 750]);

    EmployeeDailyLaberiEntry::factory()->create([
        'employee_id' => $employee->id,
        'laberi_date' => now()->startOfWeek()->toDateString(),
        'daily_shift' => DailyShift::Full,
    ]);

    $this->actingAs($user)
        ->get(route('employee-payroll.index', ['range' => 'current']))
        ->assertSuccessful()
        ->assertSee('Advance')
        ->assertSee('Bonus')
        ->assertSee('Payroll Type')
        ->assertSee('Weekly')
        ->assertSee('Shahbaz');
});

it('records a payment without client employee_id using the route employee', function () {
    $user = adminUser();
    $employee = Employee::factory()->create();

    $this->actingAs($user)
        ->post(route('employees.paid-laberi.store', $employee), [
            'amount' => 250,
            'paid_date' => now()->toDateString(),
            'payment_type' => PaymentType::Salary->value,
        ])
        ->assertRedirect(route('employees.paid-laberi.index', $employee));

    $this->assertDatabaseHas('employee_paid_laberis', [
        'employee_id' => $employee->id,
        'amount' => 250,
        'payment_type' => PaymentType::Salary->value,
    ]);
});

it('blocks payment creation on a locked payroll date', function () {
    $user = adminUser();
    $employee = Employee::factory()->create();
    $weekStart = now()->startOfWeek()->toDateString();
    $weekEnd = now()->endOfWeek()->toDateString();

    PayrollLock::create([
        'week_start_date' => $weekStart,
        'week_end_date' => $weekEnd,
        'locked_by' => $user->id,
        'locked_at' => now(),
    ]);

    $this->actingAs($user)
        ->from(route('employees.paid-laberi.create', $employee))
        ->post(route('employees.paid-laberi.store', $employee), [
            'amount' => 100,
            'paid_date' => $weekStart,
            'payment_type' => PaymentType::Advance->value,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('paid_date');
});

it('hides bulk payment when the payroll period is locked', function () {
    $user = adminUser();
    $weekStart = now()->startOfWeek()->toDateString();
    $weekEnd = now()->endOfWeek()->toDateString();

    PayrollLock::create([
        'week_start_date' => $weekStart,
        'week_end_date' => $weekEnd,
        'locked_by' => $user->id,
        'locked_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('employee-payroll.index', ['range' => 'current']))
        ->assertSuccessful()
        ->assertDontSee('Bulk Payment')
        ->assertSee('Unlock Week');
});

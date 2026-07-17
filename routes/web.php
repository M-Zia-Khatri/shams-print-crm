<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDailyLaberiController;
use App\Http\Controllers\EmployeePaidLaberiController;
use App\Http\Controllers\EmployeePayrollController;
use App\Http\Controllers\EmployeeShiftController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ItemEntryController;
use App\Http\Controllers\ItemPaymentReceivedController;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->withoutMiddleware(Authenticate::class);
Route::post('/login', [AuthController::class, 'login'])->name('login.store')->withoutMiddleware(Authenticate::class);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->withoutMiddleware(Authenticate::class);

Route::get('/', function () {
    return view('home');
})->name('home');

Route::middleware('role:viewer,admin,super_admin')->group(function (): void {
    Route::get('/item-entries', [ItemEntryController::class, 'index'])->name('item-entries.index');

    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->whereNumber('employee')->name('employees.show');
    Route::get('/employees/{employee}/daily-laberi', [EmployeeDailyLaberiController::class, 'index'])->whereNumber('employee')->name('employees.daily-laberi.index');
    Route::get('/employees/{employee}/paid-laberi', [EmployeePaidLaberiController::class, 'index'])->whereNumber('employee')->name('employees.paid-laberi.index');
    Route::get('/employee-payroll', [EmployeePayrollController::class, 'index'])->name('employee-payroll.index');
});

Route::middleware('role:admin,super_admin')->group(function (): void {
    Route::get('/item-entries/create', [ItemEntryController::class, 'create'])->name('item-entries.create');
    Route::post('/item-entries', [ItemEntryController::class, 'store'])->name('item-entries.store');
    Route::get('/item-entries/{itemEntry}/edit', [ItemEntryController::class, 'edit'])->name('item-entries.edit');
    Route::put('/item-entries/{itemEntry}', [ItemEntryController::class, 'update'])->name('item-entries.update');
    Route::delete('/item-entries/{itemEntry}', [ItemEntryController::class, 'destroy'])->name('item-entries.destroy');

    Route::post('/item-payment-receiveds', [ItemPaymentReceivedController::class, 'store'])->name('item-payment-receiveds.store');
    Route::get('/item-payment-receiveds/{itemPaymentReceived}/edit', [ItemPaymentReceivedController::class, 'edit'])->name('item-payment-receiveds.edit');
    Route::put('/item-payment-receiveds/{itemPaymentReceived}', [ItemPaymentReceivedController::class, 'update'])->name('item-payment-receiveds.update');
    Route::delete('/item-payment-receiveds/{itemPaymentReceived}', [ItemPaymentReceivedController::class, 'destroy'])->name('item-payment-receiveds.destroy');

    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Employee management
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees/create', [EmployeeController::class, 'store'])->name('employees.create.store');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->whereNumber('employee')->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->whereNumber('employee')->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->whereNumber('employee')->name('employees.destroy');

    // Bulk shift entry
    Route::get('/employees/shifts/create', [EmployeeShiftController::class, 'create'])->name('employees.shifts.create');
    Route::post('/employees/shifts/create', [EmployeeShiftController::class, 'store'])->name('employees.shifts.store');

    // Per-employee daily laberi
    Route::get('/employees/{employee}/daily-laberi/create', [EmployeeDailyLaberiController::class, 'create'])->whereNumber('employee')->name('employees.daily-laberi.create');
    Route::post('/employees/{employee}/daily-laberi/create', [EmployeeDailyLaberiController::class, 'store'])->whereNumber('employee')->name('employees.daily-laberi.store');
    Route::delete('/employees/{employee}/daily-laberi/{entry}', [EmployeeDailyLaberiController::class, 'destroy'])->whereNumber('employee')->name('employees.daily-laberi.destroy');

    // Per-employee paid laberi
    Route::get('/employees/{employee}/paid-laberi/create', [EmployeePaidLaberiController::class, 'create'])->whereNumber('employee')->name('employees.paid-laberi.create');
    Route::post('/employees/{employee}/paid-laberi/create', [EmployeePaidLaberiController::class, 'store'])->whereNumber('employee')->name('employees.paid-laberi.store');
    Route::get('/employees/{employee}/paid-laberi/{payment}/edit', [EmployeePaidLaberiController::class, 'edit'])->whereNumber('employee')->name('employees.paid-laberi.edit');
    Route::put('/employees/{employee}/paid-laberi/{payment}', [EmployeePaidLaberiController::class, 'update'])->whereNumber('employee')->name('employees.paid-laberi.update');
    Route::delete('/employees/{employee}/paid-laberi/{payment}', [EmployeePaidLaberiController::class, 'destroy'])->whereNumber('employee')->name('employees.paid-laberi.destroy');

    // Bulk payment across employees (needed for feature requirement 3)
    Route::get('/employee-paid-laberi/bulk-create', [EmployeePaidLaberiController::class, 'bulkCreate'])->name('employee-paid-laberi.bulk-create');
    Route::post('/employee-paid-laberi/bulk-create', [EmployeePaidLaberiController::class, 'bulkStore'])->name('employee-paid-laberi.bulk-store');

    // Weekly payroll lock/unlock
    Route::post('/employee-payroll/lock', [EmployeePayrollController::class, 'lock'])->name('employee-payroll.lock');
    Route::post('/employee-payroll/unlock', [EmployeePayrollController::class, 'unlock'])->name('employee-payroll.unlock');
});

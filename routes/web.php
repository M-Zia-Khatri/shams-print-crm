<?php

use App\Http\Controllers\AuthController;
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
});

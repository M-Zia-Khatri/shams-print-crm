<?php

use App\Http\Controllers\AuthController;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->withoutMiddleware(Authenticate::class);
Route::post('/login', [AuthController::class, 'login'])->name('login.store')->withoutMiddleware(Authenticate::class);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->withoutMiddleware(Authenticate::class);

Route::get('/', function () {
    return view('home');
})->name('home');

<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Auth Module API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for the Auth module. These
| routes are loaded by the AuthServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

Route::prefix('api/v1/auth')->middleware(['api'])->group(function () {
    // Public routes
    Route::post('register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');

    // Protected routes
    Route::middleware(['auth'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('user', [AuthController::class, 'user'])->name('auth.user');
        Route::post('refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
    });
});
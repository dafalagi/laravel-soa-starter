<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\Admin\UserController;

Route::prefix('user')->group(function () {
    Route::post('', [UserController::class, 'store'])->name('admin.auth.user.store');

    Route::get('{user_uuid}', [UserController::class, 'show'])->name('admin.auth.user.show');
    Route::get('', [UserController::class, 'index'])->name('admin.auth.user.index');
});
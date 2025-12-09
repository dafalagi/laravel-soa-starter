<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\Admin\UserController;

Route::prefix('users')->group(function () {
    Route::post('', [UserController::class, 'store'])->name('admin.auth.user.store');
    Route::put('{user_uuid}', [UserController::class, 'update'])->name('admin.auth.user.update');
    Route::delete('{user_uuid}', [UserController::class, 'destroy'])->name('admin.auth.user.delete');

    Route::get('{user_uuid}', [UserController::class, 'show'])->name('admin.auth.user.show');
    Route::get('', [UserController::class, 'index'])->name('admin.auth.user.index');
});
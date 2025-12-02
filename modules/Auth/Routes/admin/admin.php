<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    require __DIR__ . '/auth/auth.php';

    Route::middleware(['auth:api', 'token.admin'])->group(function () {
        // 
    });
});
<?php

use Illuminate\Support\Facades\Route;

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

Route::prefix('api/v0')->middleware(['api'])->group(function () {
    require __DIR__ . '/admin/admin.php';
});
<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuditLogController;
use Modules\Auth\Http\Controllers\AuthController;

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/password/request', [AuthController::class, 'forgotPassword']);
    Route::post('auth/password/reset', [AuthController::class, 'resetPassword']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('users/me', [AuthController::class, 'me']);
        Route::patch('users/me', [AuthController::class, 'updateProfile']);

        Route::get('audit-logs', [AuditLogController::class, 'index']);
    });
});

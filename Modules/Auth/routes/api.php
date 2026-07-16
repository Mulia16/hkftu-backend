<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuditLogController;
use Modules\Auth\Http\Controllers\DashboardController;
use Modules\Auth\Http\Controllers\AuthController;
use Modules\Auth\Http\Controllers\LearnerController;
use Modules\Auth\Http\Controllers\MfaController;

Route::prefix('v1')->group(function () {
    Route::middleware(['throttle:auth'])->group(function () {
        Route::post('auth/login', [AuthController::class, 'login']);
        Route::post('auth/register', [AuthController::class, 'register']);
        Route::post('auth/password/request', [AuthController::class, 'forgotPassword']);
        Route::post('auth/password/reset', [AuthController::class, 'resetPassword']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('users/me', [AuthController::class, 'me']);
        Route::patch('users/me', [AuthController::class, 'updateProfile']);

        Route::get('audit-logs', [AuditLogController::class, 'index']);

        Route::get('dashboard', [DashboardController::class, 'index']);

        Route::get('learners/me', [LearnerController::class, 'myProfile']);
        Route::patch('learners/me', [LearnerController::class, 'updateMyProfile']);
        Route::get('learners', [LearnerController::class, 'index']);
        Route::post('learners', [LearnerController::class, 'store']);
        Route::get('learners/{id}', [LearnerController::class, 'show']);
        Route::patch('learners/{id}', [LearnerController::class, 'update']);

        Route::get('dependents', [LearnerController::class, 'myDependents']);
        Route::post('dependents', [LearnerController::class, 'storeDependent']);

        Route::post('auth/mfa/enable', [MfaController::class, 'enable']);
        Route::post('auth/mfa/verify', [MfaController::class, 'verify']);
        Route::post('auth/mfa/disable', [MfaController::class, 'disable']);
    });
});

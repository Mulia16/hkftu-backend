<?php

use Illuminate\Support\Facades\Route;
use Modules\Attendance\Http\Controllers\AttendanceController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('classes/{classId}/attendance', [AttendanceController::class, 'grid']);
    Route::put('classes/{classId}/attendance', [AttendanceController::class, 'save']);
    Route::post('attendance/sessions/{sessionId}/submit', [AttendanceController::class, 'submit']);

    Route::get('attendance/{id}', [AttendanceController::class, 'show']);
    Route::put('attendance/{id}', [AttendanceController::class, 'update']);

    Route::get('attendance/learner-history', [AttendanceController::class, 'learnerHistory']);
});

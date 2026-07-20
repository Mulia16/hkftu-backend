<?php

use Illuminate\Support\Facades\Route;
use Modules\Attendance\Http\Controllers\AttendanceController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('classes/{classId}/attendance', [AttendanceController::class, 'grid'])
        ->middleware('role:system_admin,centre_manager,counter_staff,instructor');
    Route::put('classes/{classId}/attendance', [AttendanceController::class, 'save'])
        ->middleware('role:system_admin,centre_manager,counter_staff,instructor');
    Route::post('attendance/sessions/{sessionId}/submit', [AttendanceController::class, 'submit'])
        ->middleware('role:system_admin,centre_manager,counter_staff,instructor');

    Route::get('attendance/{id}', [AttendanceController::class, 'show'])
        ->middleware('role:system_admin,centre_manager,counter_staff,instructor');
    Route::put('attendance/{id}', [AttendanceController::class, 'update'])
        ->middleware('role:system_admin,centre_manager,counter_staff,instructor');

    Route::get('attendance/learner-history', [AttendanceController::class, 'learnerHistory']);
});

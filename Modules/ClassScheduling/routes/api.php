<?php

use Illuminate\Support\Facades\Route;
use Modules\ClassScheduling\Http\Controllers\CalendarController;
use Modules\ClassScheduling\Http\Controllers\CentreController;
use Modules\ClassScheduling\Http\Controllers\ClassController;
use Modules\ClassScheduling\Http\Controllers\ClassroomController;

Route::prefix('v1')->group(function () {
    Route::middleware(['throttle:public'])->group(function () {
        Route::get('centres', [CentreController::class, 'index']);
        Route::get('centres/{centre}', [CentreController::class, 'show']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('calendar/sessions', [CalendarController::class, 'sessions']);

        Route::post('centres', [CentreController::class, 'store'])
            ->middleware('role:system_admin');
        Route::patch('centres/{centre}', [CentreController::class, 'update'])
            ->middleware('role:system_admin');
        Route::delete('centres/{centre}', [CentreController::class, 'destroy'])
            ->middleware('role:system_admin');

        Route::get('centres/{centre}/classrooms', [ClassroomController::class, 'index'])
            ->middleware('role:system_admin,course_planner,centre_manager');
        Route::get('centres/{centre}/classrooms/{classroom}', [ClassroomController::class, 'show'])
            ->middleware('role:system_admin,course_planner,centre_manager');
        Route::post('centres/{centre}/classrooms', [ClassroomController::class, 'store'])
            ->middleware('role:system_admin');
        Route::patch('centres/{centre}/classrooms/{classroom}', [ClassroomController::class, 'update'])
            ->middleware('role:system_admin');
        Route::delete('centres/{centre}/classrooms/{classroom}', [ClassroomController::class, 'destroy'])
            ->middleware('role:system_admin');

        Route::get('classes', [ClassController::class, 'index']);
        Route::post('classes', [ClassController::class, 'store'])
            ->middleware('role:course_planner,centre_manager,system_admin');
        Route::get('classes/{id}', [ClassController::class, 'show']);
        Route::patch('classes/{id}', [ClassController::class, 'update'])
            ->middleware('role:course_planner,centre_manager,system_admin');
        Route::delete('classes/{id}', [ClassController::class, 'destroy'])
            ->middleware('role:course_planner,centre_manager,system_admin');
        Route::post('classes/{id}/publish', [ClassController::class, 'publish'])
            ->middleware('role:centre_manager,system_admin');
        Route::post('classes/{id}/cancel', [ClassController::class, 'cancel'])
            ->middleware('role:centre_manager,system_admin');
        Route::post('classes/{id}/complete', [ClassController::class, 'complete'])
            ->middleware('role:centre_manager,system_admin');
        Route::get('classes/{id}/sessions', [ClassController::class, 'sessions']);
        Route::post('classes/{id}/clash-check', [ClassController::class, 'clashCheck'])
            ->middleware('role:course_planner,centre_manager,system_admin');
        Route::post('classes/{classId}/clashes/{clashId}/resolve', [ClassController::class, 'resolveClash'])
            ->middleware('role:course_planner,centre_manager,system_admin');
        Route::get('classes/{id}/availability', [ClassController::class, 'availability']);
    });
});

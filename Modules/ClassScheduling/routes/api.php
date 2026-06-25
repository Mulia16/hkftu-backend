<?php

use Illuminate\Support\Facades\Route;
use Modules\ClassScheduling\Http\Controllers\CentreController;
use Modules\ClassScheduling\Http\Controllers\ClassController;
use Modules\ClassScheduling\Http\Controllers\ClassroomController;

Route::prefix('v1')->group(function () {
    Route::middleware(['throttle:public'])->group(function () {
        Route::get('centres', [CentreController::class, 'index']);
        Route::get('centres/{centre}', [CentreController::class, 'show']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('centres', [CentreController::class, 'store']);
        Route::patch('centres/{centre}', [CentreController::class, 'update']);
        Route::delete('centres/{centre}', [CentreController::class, 'destroy']);

        Route::get('centres/{centre}/classrooms', [ClassroomController::class, 'index']);
        Route::get('centres/{centre}/classrooms/{classroom}', [ClassroomController::class, 'show']);
        Route::post('centres/{centre}/classrooms', [ClassroomController::class, 'store']);
        Route::patch('centres/{centre}/classrooms/{classroom}', [ClassroomController::class, 'update']);
        Route::delete('centres/{centre}/classrooms/{classroom}', [ClassroomController::class, 'destroy']);

        Route::get('classes', [ClassController::class, 'index']);
        Route::post('classes', [ClassController::class, 'store']);
        Route::get('classes/{id}', [ClassController::class, 'show']);
        Route::patch('classes/{id}', [ClassController::class, 'update']);
        Route::delete('classes/{id}', [ClassController::class, 'destroy']);
        Route::post('classes/{id}/publish', [ClassController::class, 'publish']);
        Route::get('classes/{id}/sessions', [ClassController::class, 'sessions']);
        Route::post('classes/{id}/clash-check', [ClassController::class, 'clashCheck']);
        Route::post('classes/{classId}/clashes/{clashId}/resolve', [ClassController::class, 'resolveClash']);
        Route::get('classes/{id}/availability', [ClassController::class, 'availability']);
    });
});

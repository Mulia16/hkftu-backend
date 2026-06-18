<?php

use Illuminate\Support\Facades\Route;
use Modules\ClassScheduling\Http\Controllers\CentreController;
use Modules\ClassScheduling\Http\Controllers\ClassroomController;

Route::prefix('v1')->group(function () {
    Route::get('centres', [CentreController::class, 'index']);
    Route::get('centres/{centre}', [CentreController::class, 'show']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('centres', [CentreController::class, 'store']);
        Route::patch('centres/{centre}', [CentreController::class, 'update']);
        Route::delete('centres/{centre}', [CentreController::class, 'destroy']);

        Route::get('centres/{centre}/classrooms', [ClassroomController::class, 'index']);
        Route::get('centres/{centre}/classrooms/{classroom}', [ClassroomController::class, 'show']);
        Route::post('centres/{centre}/classrooms', [ClassroomController::class, 'store']);
        Route::patch('centres/{centre}/classrooms/{classroom}', [ClassroomController::class, 'update']);
        Route::delete('centres/{centre}/classrooms/{classroom}', [ClassroomController::class, 'destroy']);
    });
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\CourseCatalogue\Http\Controllers\CategoryController;
use Modules\CourseCatalogue\Http\Controllers\SeasonController;
use Modules\CourseCatalogue\Http\Controllers\SubjectController;

Route::prefix('v1')->group(function () {
    Route::get('seasons', [SeasonController::class, 'index']);
    Route::get('seasons/{season}', [SeasonController::class, 'show']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('seasons', [SeasonController::class, 'store']);
        Route::patch('seasons/{season}', [SeasonController::class, 'update']);
        Route::delete('seasons/{season}', [SeasonController::class, 'destroy']);

        Route::post('categories', [CategoryController::class, 'store']);
        Route::patch('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

        Route::apiResource('subjects', SubjectController::class);
    });
});

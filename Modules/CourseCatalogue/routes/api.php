<?php

use App\Http\Controllers\NoticeController;
use Illuminate\Support\Facades\Route;
use Modules\CourseCatalogue\Http\Controllers\CategoryController;
use Modules\CourseCatalogue\Http\Controllers\CourseController;
use Modules\CourseCatalogue\Http\Controllers\CourseTextController;
use Modules\CourseCatalogue\Http\Controllers\SeasonController;
use Modules\CourseCatalogue\Http\Controllers\SubjectController;

Route::prefix('v1')->group(function () {
    Route::get('seasons', [SeasonController::class, 'index']);
    Route::get('seasons/{season}', [SeasonController::class, 'show']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    Route::get('courses/search', [CourseController::class, 'search']);
    Route::get('courses/{courseCode}', [CourseController::class, 'detail']);
    Route::get('notices', [NoticeController::class, 'index']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('seasons', [SeasonController::class, 'store']);
        Route::patch('seasons/{season}', [SeasonController::class, 'update']);
        Route::delete('seasons/{season}', [SeasonController::class, 'destroy']);

        Route::post('categories', [CategoryController::class, 'store']);
        Route::patch('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

        Route::apiResource('subjects', SubjectController::class);

        Route::apiResource('courses', CourseController::class);

        Route::get('course-texts/{subjectId}', [CourseTextController::class, 'index']);
        Route::get('course-texts/{subjectId}/{versionId}', [CourseTextController::class, 'show']);
        Route::post('course-texts/{subjectId}', [CourseTextController::class, 'store']);
        Route::patch('course-texts/{subjectId}/{versionId}', [CourseTextController::class, 'update']);

        Route::get('admin/notices', [NoticeController::class, 'adminIndex']);
        Route::post('notices', [NoticeController::class, 'store']);
        Route::patch('notices/{id}', [NoticeController::class, 'update']);
        Route::delete('notices/{id}', [NoticeController::class, 'destroy']);
    });
});

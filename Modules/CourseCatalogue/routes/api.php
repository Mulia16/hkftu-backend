<?php

use Illuminate\Support\Facades\Route;
use Modules\CourseCatalogue\Http\Controllers\CategoryController;
use Modules\CourseCatalogue\Http\Controllers\CourseController;
use Modules\CourseCatalogue\Http\Controllers\CourseTextController;
use Modules\CourseCatalogue\Http\Controllers\NoticeController;
use Modules\CourseCatalogue\Http\Controllers\SeasonController;
use Modules\CourseCatalogue\Http\Controllers\SubjectController;

Route::prefix('v1')->group(function () {
    Route::middleware(['throttle:public'])->group(function () {
        Route::get('seasons', [SeasonController::class, 'index']);
        Route::get('seasons/{season}', [SeasonController::class, 'show']);
        Route::get('categories', [CategoryController::class, 'index']);
        Route::get('categories/{category}', [CategoryController::class, 'show']);
        Route::get('courses/search', [CourseController::class, 'search']);
        Route::get('courses/{courseCode}', [CourseController::class, 'detail']);
        Route::get('notices', [NoticeController::class, 'index']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('seasons', [SeasonController::class, 'store'])
            ->middleware('role:course_planner,system_admin');
        Route::patch('seasons/{season}', [SeasonController::class, 'update'])
            ->middleware('role:course_planner,system_admin');
        Route::delete('seasons/{season}', [SeasonController::class, 'destroy'])
            ->middleware('role:course_planner,system_admin');

        Route::post('categories', [CategoryController::class, 'store'])
            ->middleware('role:course_planner,system_admin');
        Route::patch('categories/{category}', [CategoryController::class, 'update'])
            ->middleware('role:course_planner,system_admin');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])
            ->middleware('role:course_planner,system_admin');

        Route::get('subjects', [SubjectController::class, 'index'])
            ->middleware('role:course_planner,centre_manager,system_admin');
        Route::post('subjects', [SubjectController::class, 'store'])
            ->middleware('role:course_planner,system_admin');
        Route::get('subjects/{subject}', [SubjectController::class, 'show'])
            ->middleware('role:course_planner,centre_manager,system_admin');
        Route::patch('subjects/{subject}', [SubjectController::class, 'update'])
            ->middleware('role:course_planner,system_admin');
        Route::delete('subjects/{subject}', [SubjectController::class, 'destroy'])
            ->middleware('role:course_planner,system_admin');

        Route::get('courses', [CourseController::class, 'index'])
            ->middleware('role:course_planner,centre_manager,system_admin');
        Route::post('courses', [CourseController::class, 'store'])
            ->middleware('role:course_planner,system_admin');
        Route::get('courses/{course}', [CourseController::class, 'show'])
            ->middleware('role:course_planner,centre_manager,system_admin');
        Route::patch('courses/{course}', [CourseController::class, 'update'])
            ->middleware('role:course_planner,system_admin');
        Route::delete('courses/{course}', [CourseController::class, 'destroy'])
            ->middleware('role:course_planner,system_admin');

        Route::get('course-texts/{subjectId}', [CourseTextController::class, 'index'])
            ->middleware('role:course_planner,centre_manager,system_admin');
        Route::get('course-texts/{subjectId}/{versionId}', [CourseTextController::class, 'show'])
            ->middleware('role:course_planner,centre_manager,system_admin');
        Route::post('course-texts/{subjectId}', [CourseTextController::class, 'store'])
            ->middleware('role:course_planner,system_admin');
        Route::patch('course-texts/{subjectId}/{versionId}', [CourseTextController::class, 'update'])
            ->middleware('role:course_planner,centre_manager,system_admin');

        Route::get('admin/notices', [NoticeController::class, 'adminIndex'])
            ->middleware('role:system_admin,centre_manager');
        Route::post('notices', [NoticeController::class, 'store'])
            ->middleware('role:system_admin');
        Route::patch('notices/{id}', [NoticeController::class, 'update'])
            ->middleware('role:system_admin');
        Route::delete('notices/{id}', [NoticeController::class, 'destroy'])
            ->middleware('role:system_admin');
    });
});

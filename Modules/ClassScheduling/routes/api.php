<?php

use Illuminate\Support\Facades\Route;
use Modules\ClassScheduling\Http\Controllers\ClassSchedulingController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('classschedulings', ClassSchedulingController::class)->names('classscheduling');
});

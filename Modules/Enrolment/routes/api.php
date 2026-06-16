<?php

use Illuminate\Support\Facades\Route;
use Modules\Enrolment\Http\Controllers\EnrolmentController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('enrolments', EnrolmentController::class)->names('enrolment');
});

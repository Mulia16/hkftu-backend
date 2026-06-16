<?php

use Illuminate\Support\Facades\Route;
use Modules\Enrolment\Http\Controllers\EnrolmentController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('enrolments', EnrolmentController::class)->names('enrolment');
});

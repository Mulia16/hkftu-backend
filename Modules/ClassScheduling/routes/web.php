<?php

use Illuminate\Support\Facades\Route;
use Modules\ClassScheduling\Http\Controllers\ClassSchedulingController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('classschedulings', ClassSchedulingController::class)->names('classscheduling');
});

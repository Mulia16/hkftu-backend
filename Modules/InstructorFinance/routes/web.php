<?php

use Illuminate\Support\Facades\Route;
use Modules\InstructorFinance\Http\Controllers\InstructorFinanceController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('instructorfinances', InstructorFinanceController::class)->names('instructorfinance');
});

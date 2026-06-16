<?php

use Illuminate\Support\Facades\Route;
use Modules\InstructorFinance\Http\Controllers\InstructorFinanceController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('instructorfinances', InstructorFinanceController::class)->names('instructorfinance');
});

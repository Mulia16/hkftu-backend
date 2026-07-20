<?php

use Illuminate\Support\Facades\Route;
use Modules\InstructorFinance\Http\Controllers\ChequeRecordController;
use Modules\InstructorFinance\Http\Controllers\InstructorController;
use Modules\InstructorFinance\Http\Controllers\InstructorFeeCalculationController;
use Modules\InstructorFinance\Http\Controllers\InstructorFeeRuleController;
use Modules\InstructorFinance\Http\Controllers\InstructorPaymentBatchController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('instructors', [InstructorController::class, 'index'])
        ->middleware('role:system_admin,centre_manager,course_planner');
    Route::get('instructors/{id}', [InstructorController::class, 'show'])
        ->middleware('role:system_admin,centre_manager,course_planner');
    Route::get('instructors/{id}/teaching-summary', [InstructorController::class, 'teachingSummary'])
        ->middleware('role:system_admin,centre_manager,course_planner,instructor');
    Route::get('instructors/{id}/sign-in-sheet', [InstructorController::class, 'signInSheet'])
        ->middleware('role:system_admin,centre_manager,counter_staff,instructor');
    Route::post('instructor-contracts/generate', [InstructorController::class, 'generateContract'])
        ->middleware('role:system_admin,finance_staff');

    Route::apiResource('instructor-fee-rules', InstructorFeeRuleController::class)
        ->middleware('role:system_admin,finance_staff');

    Route::post('instructor-fees/calculate', [InstructorFeeCalculationController::class, 'calculate'])
        ->middleware('role:system_admin,finance_staff');
    Route::get('instructor-fee-items', [InstructorFeeCalculationController::class, 'index'])
        ->middleware('role:system_admin,finance_staff,centre_manager');
    Route::patch('instructor-fee-items/{id}/adjustment', [InstructorFeeCalculationController::class, 'updateAdjustment'])
        ->middleware('role:system_admin,finance_staff');

    Route::apiResource('instructor-payment-batches', InstructorPaymentBatchController::class)->only(['index', 'store', 'show'])
        ->middleware('role:system_admin,finance_staff');
    Route::post('instructor-payment-batches/{id}/approve', [InstructorPaymentBatchController::class, 'approve'])
        ->middleware('role:system_admin,finance_staff');

    Route::get('cheque-records', [ChequeRecordController::class, 'index'])
        ->middleware('role:system_admin,finance_staff');
    Route::post('cheque-records/{id}/print', [ChequeRecordController::class, 'markPrinted'])
        ->middleware('role:system_admin,finance_staff');
});

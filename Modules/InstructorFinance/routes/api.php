<?php

use Illuminate\Support\Facades\Route;
use Modules\InstructorFinance\Http\Controllers\ChequeRecordController;
use Modules\InstructorFinance\Http\Controllers\InstructorController;
use Modules\InstructorFinance\Http\Controllers\InstructorFeeCalculationController;
use Modules\InstructorFinance\Http\Controllers\InstructorFeeRuleController;
use Modules\InstructorFinance\Http\Controllers\InstructorPaymentBatchController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('instructors', [InstructorController::class, 'index']);
    Route::get('instructors/{id}', [InstructorController::class, 'show']);
    Route::get('instructors/{id}/teaching-summary', [InstructorController::class, 'teachingSummary']);
    Route::post('instructor-contracts/generate', [InstructorController::class, 'generateContract']);

    Route::apiResource('instructor-fee-rules', InstructorFeeRuleController::class);

    Route::post('instructor-fees/calculate', [InstructorFeeCalculationController::class, 'calculate']);
    Route::get('instructor-fee-items', [InstructorFeeCalculationController::class, 'index']);
    Route::patch('instructor-fee-items/{id}/adjustment', [InstructorFeeCalculationController::class, 'updateAdjustment']);

    Route::apiResource('instructor-payment-batches', InstructorPaymentBatchController::class)->only(['index', 'store', 'show']);
    Route::post('instructor-payment-batches/{id}/approve', [InstructorPaymentBatchController::class, 'approve']);

    Route::get('cheque-records', [ChequeRecordController::class, 'index']);
    Route::post('cheque-records/{id}/print', [ChequeRecordController::class, 'markPrinted']);
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\AdminPaymentController;
use Modules\Payment\Http\Controllers\PaymentController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::post('payments/intents', [PaymentController::class, 'createIntent']);
    Route::post('payments/razor', [PaymentController::class, 'processRazerMs']);
    Route::get('payments/intents/{id}', [PaymentController::class, 'showIntent']);
    Route::post('payments/manual-upload', [PaymentController::class, 'uploadProof']);
    Route::get('payments', [PaymentController::class, 'myPayments']);

    Route::get('admin/payments', [AdminPaymentController::class, 'index']);
    Route::get('admin/payments/{id}', [AdminPaymentController::class, 'show']);
    Route::post('admin/payments/{transactionId}/verify', [AdminPaymentController::class, 'verify']);
});

Route::prefix('v1')->group(function () {
    Route::get('payments/gateway/return', [PaymentController::class, 'handleGatewayReturn']);
    Route::post('payments/gateway/callback', [PaymentController::class, 'handleGatewayReturn']);
});

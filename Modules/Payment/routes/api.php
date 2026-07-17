<?php

use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\AdminPaymentController;
use Modules\Payment\Http\Controllers\CouponController;
use Modules\Payment\Http\Controllers\PaymentController;
use Modules\Payment\Http\Controllers\ReceiptController;
use Modules\Payment\Http\Controllers\ReconciliationController;
use Modules\Payment\Http\Controllers\RefundController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::post('payments/intents', [PaymentController::class, 'createIntent']);
    Route::post('payments/razor', [PaymentController::class, 'processRazerMs']);
    Route::get('payments/intents/{id}', [PaymentController::class, 'showIntent']);
    Route::post('payments/manual-upload', [PaymentController::class, 'uploadProof']);
    Route::get('payments', [PaymentController::class, 'myPayments']);

    Route::get('receipts/{receiptNo}', [ReceiptController::class, 'show']);
    Route::get('receipts/{receiptNo}/download', [ReceiptController::class, 'download']);
    Route::get('my-receipts', [ReceiptController::class, 'myReceipts']);

    Route::get('admin/payments', [AdminPaymentController::class, 'index']);
    Route::get('admin/payments/{id}', [AdminPaymentController::class, 'show']);
    Route::post('admin/payments/{transactionId}/verify', [AdminPaymentController::class, 'verify']);

    Route::get('refunds', [RefundController::class, 'index']);
    Route::post('refunds', [RefundController::class, 'store']);
    Route::get('refunds/{id}', [RefundController::class, 'show']);
    Route::post('refunds/{id}/approve', [RefundController::class, 'approve']);
    Route::post('refunds/{id}/execute', [RefundController::class, 'execute']);
    Route::post('refunds/{id}/reject', [RefundController::class, 'reject']);
    Route::get('my-refunds', [RefundController::class, 'myRefunds']);

    Route::get('coupons', [CouponController::class, 'index']);
    Route::post('coupons', [CouponController::class, 'store']);
    Route::get('coupons/{id}', [CouponController::class, 'show']);
    Route::patch('coupons/{id}', [CouponController::class, 'update']);
    Route::post('coupons/{id}/generate-codes', [CouponController::class, 'generateCodes']);
    Route::post('coupons/validate', [CouponController::class, 'validateCoupon']);
    Route::post('coupons/redeem', [CouponController::class, 'redeem']);

    Route::get('reconciliation/batches', [ReconciliationController::class, 'index']);
    Route::post('reconciliation/batches', [ReconciliationController::class, 'store']);
    Route::get('reconciliation/batches/{id}', [ReconciliationController::class, 'show']);
    Route::post('reconciliation/batches/{id}/match', [ReconciliationController::class, 'match']);
    Route::post('reconciliation/batches/{id}/close', [ReconciliationController::class, 'close']);
    Route::get('reconciliation/batches/{id}/exceptions', [ReconciliationController::class, 'exceptions']);
});

Route::prefix('v1')->group(function () {
    Route::get('payments/gateway/return', [PaymentController::class, 'handleGatewayReturn']);
    Route::post('payments/gateway/callback', [PaymentController::class, 'handleGatewayReturn']);
});

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

    Route::get('admin/payments', [AdminPaymentController::class, 'index'])
        ->middleware('role:system_admin,centre_manager,finance_staff');
    Route::get('admin/payments/{id}', [AdminPaymentController::class, 'show'])
        ->middleware('role:system_admin,centre_manager,finance_staff');
    Route::post('admin/payments/{transactionId}/verify', [AdminPaymentController::class, 'verify'])
        ->middleware('role:system_admin,finance_staff');

    Route::get('refunds', [RefundController::class, 'index'])
        ->middleware('role:system_admin,centre_manager,finance_staff');
    Route::post('refunds', [RefundController::class, 'store']);
    Route::get('refunds/{id}', [RefundController::class, 'show']);
    Route::post('refunds/{id}/approve', [RefundController::class, 'approve'])
        ->middleware('role:system_admin,centre_manager');
    Route::post('refunds/{id}/execute', [RefundController::class, 'execute'])
        ->middleware('role:system_admin,finance_staff');
    Route::post('refunds/{id}/reject', [RefundController::class, 'reject'])
        ->middleware('role:system_admin,centre_manager');
    Route::get('my-refunds', [RefundController::class, 'myRefunds']);

    Route::get('coupons', [CouponController::class, 'index'])
        ->middleware('role:system_admin,centre_manager,finance_staff,course_planner');
    Route::post('coupons', [CouponController::class, 'store'])
        ->middleware('role:system_admin,finance_staff');
    Route::get('coupons/{id}', [CouponController::class, 'show'])
        ->middleware('role:system_admin,centre_manager,finance_staff');
    Route::patch('coupons/{id}', [CouponController::class, 'update'])
        ->middleware('role:system_admin,finance_staff');
    Route::post('coupons/{id}/generate-codes', [CouponController::class, 'generateCodes'])
        ->middleware('role:system_admin,finance_staff');
    Route::post('coupons/validate', [CouponController::class, 'validateCoupon']);
    Route::post('coupons/redeem', [CouponController::class, 'redeem']);

    Route::get('reconciliation/batches', [ReconciliationController::class, 'index'])
        ->middleware('role:system_admin,finance_staff');
    Route::post('reconciliation/batches', [ReconciliationController::class, 'store'])
        ->middleware('role:system_admin,finance_staff');
    Route::get('reconciliation/batches/{id}', [ReconciliationController::class, 'show'])
        ->middleware('role:system_admin,finance_staff');
    Route::post('reconciliation/batches/{id}/match', [ReconciliationController::class, 'match'])
        ->middleware('role:system_admin,finance_staff');
    Route::post('reconciliation/batches/{id}/close', [ReconciliationController::class, 'close'])
        ->middleware('role:system_admin,finance_staff');
    Route::get('reconciliation/batches/{id}/exceptions', [ReconciliationController::class, 'exceptions'])
        ->middleware('role:system_admin,finance_staff');
});

Route::prefix('v1')->group(function () {
    Route::get('payments/gateway/return', [PaymentController::class, 'handleGatewayReturn']);
    Route::post('payments/gateway/callback', [PaymentController::class, 'handleGatewayReturn']);
});

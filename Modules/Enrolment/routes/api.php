<?php

use Illuminate\Support\Facades\Route;
use Modules\Enrolment\Http\Controllers\CounterEnrolmentController;
use Modules\Enrolment\Http\Controllers\EnrolmentController;
use Modules\Enrolment\Http\Controllers\SeatReservationController;
use Modules\Enrolment\Http\Controllers\TransferController;
use Modules\Enrolment\Http\Controllers\WaitlistController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::post('seat-reservations', [SeatReservationController::class, 'store']);
    Route::get('seat-reservations/{id}', [SeatReservationController::class, 'show']);
    Route::post('seat-reservations/{id}/cancel', [SeatReservationController::class, 'cancel']);

    Route::get('enrolments', [EnrolmentController::class, 'index']);
    Route::post('enrolments', [EnrolmentController::class, 'store']);
    Route::get('enrolments/{id}', [EnrolmentController::class, 'show']);
    Route::post('enrolments/{id}/confirm', [EnrolmentController::class, 'confirm']);
    Route::post('enrolments/{id}/cancel', [EnrolmentController::class, 'cancel']);

    Route::post('counter/enrolments', [CounterEnrolmentController::class, 'store']);

    Route::get('transfers', [TransferController::class, 'index']);
    Route::post('transfers', [TransferController::class, 'store']);
    Route::get('transfers/{id}', [TransferController::class, 'show']);
    Route::post('transfers/{id}/approve', [TransferController::class, 'approve']);
    Route::post('transfers/{id}/reject', [TransferController::class, 'reject']);

    Route::post('waitlists', [WaitlistController::class, 'store']);
    Route::post('waitlists/{id}/offer', [WaitlistController::class, 'offer']);
});

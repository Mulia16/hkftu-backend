<?php

use Illuminate\Support\Facades\Route;
use Modules\Enrolment\Http\Controllers\EnrolmentController;
use Modules\Enrolment\Http\Controllers\SeatReservationController;
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

    Route::post('waitlists', [WaitlistController::class, 'store']);
    Route::post('waitlists/{id}/offer', [WaitlistController::class, 'offer']);
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/send', [NotificationController::class, 'send']);
    Route::post('support/tickets', [NotificationController::class, 'storeTicket']);
    Route::get('support/tickets', [NotificationController::class, 'tickets']);
    Route::post('support/tickets/{id}/respond', [NotificationController::class, 'respondTicket']);
});

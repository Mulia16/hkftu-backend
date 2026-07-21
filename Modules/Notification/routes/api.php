<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('notifications', [NotificationController::class, 'index'])
        ->middleware('role:system_admin,centre_manager');
    Route::post('notifications/send', [NotificationController::class, 'send'])
        ->middleware('role:system_admin,centre_manager');
    Route::post('support/tickets', [NotificationController::class, 'storeTicket']);
    Route::get('support/tickets', [NotificationController::class, 'tickets']);
    Route::post('support/tickets/{id}/respond', [NotificationController::class, 'respondTicket'])
        ->middleware('role:system_admin,centre_manager,counter_staff');
});

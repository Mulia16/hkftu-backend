<?php

use Illuminate\Support\Facades\Route;
use Modules\Membership\Http\Controllers\MembershipController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::post('membership/verify', [MembershipController::class, 'verify'])
        ->middleware('role:system_admin,centre_manager,counter_staff');
    Route::get('membership/snapshots/{learnerProfileId}', [MembershipController::class, 'snapshots'])
        ->middleware('role:system_admin,centre_manager');
    Route::get('membership/verifications/{learnerProfileId}', [MembershipController::class, 'verifications'])
        ->middleware('role:system_admin,centre_manager');
});

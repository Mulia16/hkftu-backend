<?php

use Illuminate\Support\Facades\Route;
use Modules\Membership\Http\Controllers\MembershipController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::post('membership/verify', [MembershipController::class, 'verify']);
    Route::get('membership/snapshots/{learnerProfileId}', [MembershipController::class, 'snapshots']);
    Route::get('membership/verifications/{learnerProfileId}', [MembershipController::class, 'verifications']);
});

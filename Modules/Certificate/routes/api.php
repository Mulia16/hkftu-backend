<?php

use Illuminate\Support\Facades\Route;
use Modules\Certificate\Http\Controllers\CertificateController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('certificates/eligibility/{classId}', [CertificateController::class, 'eligibility'])
        ->middleware('role:system_admin,centre_manager,counter_staff');
    Route::post('certificates/issue', [CertificateController::class, 'issue'])
        ->middleware('role:system_admin,centre_manager');
    Route::get('certificates/{id}', [CertificateController::class, 'show'])
        ->middleware('role:system_admin,centre_manager,counter_staff');
    Route::get('certificates/{id}/pdf', [CertificateController::class, 'pdf'])
        ->middleware('role:system_admin,centre_manager,counter_staff');
    Route::post('certificates/{id}/reprint', [CertificateController::class, 'reprint'])
        ->middleware('role:system_admin,centre_manager');
    Route::post('certificates/batch-pdf', [CertificateController::class, 'batchPdf'])
        ->middleware('role:system_admin,centre_manager');

    Route::get('certificates', [CertificateController::class, 'index'])
        ->middleware('role:system_admin,centre_manager,counter_staff');
    Route::get('certificate-templates', [CertificateController::class, 'templates'])
        ->middleware('role:system_admin,centre_manager');

    Route::get('my-certificates', [CertificateController::class, 'myCertificates']);
    Route::post('my-certificates/{id}/reprint-request', [CertificateController::class, 'reprintRequest']);
});

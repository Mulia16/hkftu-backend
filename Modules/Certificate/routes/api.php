<?php

use Illuminate\Support\Facades\Route;
use Modules\Certificate\Http\Controllers\CertificateController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('certificates/eligibility/{classId}', [CertificateController::class, 'eligibility']);
    Route::post('certificates/issue', [CertificateController::class, 'issue']);
    Route::get('certificates/{id}', [CertificateController::class, 'show']);
    Route::get('certificates/{id}/pdf', [CertificateController::class, 'pdf']);
    Route::post('certificates/{id}/reprint', [CertificateController::class, 'reprint']);
    Route::post('certificates/batch-pdf', [CertificateController::class, 'batchPdf']);

    Route::get('certificates', [CertificateController::class, 'index']);
    Route::get('certificate-templates', [CertificateController::class, 'templates']);

    Route::get('my-certificates', [CertificateController::class, 'myCertificates']);
    Route::post('my-certificates/{id}/reprint-request', [CertificateController::class, 'reprintRequest']);
});

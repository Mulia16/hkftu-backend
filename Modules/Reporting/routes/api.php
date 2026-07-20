<?php

use Illuminate\Support\Facades\Route;
use Modules\Reporting\Http\Controllers\JobController;
use Modules\Reporting\Http\Controllers\ReportRunController;
use Modules\Reporting\Http\Controllers\ReportTemplateController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('report-templates', [ReportTemplateController::class, 'index'])
        ->middleware('role:system_admin,centre_manager,finance_staff');
    Route::post('reports/run', [ReportRunController::class, 'store'])
        ->middleware('role:system_admin,centre_manager,finance_staff');
    Route::get('report-runs', [ReportRunController::class, 'index'])
        ->middleware('role:system_admin,centre_manager,finance_staff');
    Route::get('report-runs/{id}', [ReportRunController::class, 'show'])
        ->middleware('role:system_admin,centre_manager,finance_staff');
    Route::get('jobs/{id}', [JobController::class, 'show'])
        ->middleware('role:system_admin,centre_manager,finance_staff');
    Route::get('jobs/{id}/download', [JobController::class, 'download'])
        ->middleware('role:system_admin,centre_manager,finance_staff');
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Reporting\Http\Controllers\JobController;
use Modules\Reporting\Http\Controllers\ReportRunController;
use Modules\Reporting\Http\Controllers\ReportTemplateController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('report-templates', [ReportTemplateController::class, 'index']);
    Route::post('reports/run', [ReportRunController::class, 'store']);
    Route::get('report-runs', [ReportRunController::class, 'index']);
    Route::get('report-runs/{id}', [ReportRunController::class, 'show']);
    Route::get('jobs/{id}', [JobController::class, 'show']);
    Route::get('jobs/{id}/download', [JobController::class, 'download']);
});

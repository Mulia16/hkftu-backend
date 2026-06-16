<?php

use Illuminate\Support\Facades\Route;
use Modules\CourseCatalogue\Http\Controllers\CourseCatalogueController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('coursecatalogues', CourseCatalogueController::class)->names('coursecatalogue');
});

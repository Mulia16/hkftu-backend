<?php

use Illuminate\Support\Facades\Route;
use Modules\CourseCatalogue\Http\Controllers\CourseCatalogueController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('coursecatalogues', CourseCatalogueController::class)->names('coursecatalogue');
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Pelatihan\Http\Controllers\PelatihanController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('pelatihans', PelatihanController::class)->names('pelatihan');
});

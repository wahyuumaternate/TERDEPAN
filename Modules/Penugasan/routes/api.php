<?php

use Illuminate\Support\Facades\Route;
use Modules\Penugasan\Http\Controllers\PenugasanController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('penugasans', PenugasanController::class)->names('penugasan');
});

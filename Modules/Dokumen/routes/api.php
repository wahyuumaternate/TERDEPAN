<?php

use Illuminate\Support\Facades\Route;
use Modules\Dokumen\Http\Controllers\DokumenController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('dokumens', DokumenController::class)->names('dokumen');
});

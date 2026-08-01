<?php

use Illuminate\Support\Facades\Route;
use Modules\Penugasan\Http\Controllers\Api\PenugasanController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('penugasan', PenugasanController::class);

    Route::prefix('penugasan/{id}')->group(function () {
        Route::post('/terima', [PenugasanController::class, 'terima'])->name('penugasan.terima');
        Route::post('/tolak', [PenugasanController::class, 'tolak'])->name('penugasan.tolak');
        Route::post('/submit', [PenugasanController::class, 'submit'])->name('penugasan.submit');
        Route::post('/validasi', [PenugasanController::class, 'validasi'])->name('penugasan.validasi');
        Route::post('/progress', [PenugasanController::class, 'updateProgress'])->name('penugasan.progress');
    });
});

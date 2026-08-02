<?php

use Illuminate\Support\Facades\Route;
use Modules\Penugasan\Http\Controllers\Api\PenugasanController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Route statis/prefix khusus didaftarkan sebelum apiResource supaya tidak tertimpa wildcard {penugasan}
    Route::get('penugasan/atasan-mandiri', [PenugasanController::class, 'atasanMandiri'])->name('penugasan.atasan-mandiri');
    Route::post('penugasan/berikan-tugas-grup', [PenugasanController::class, 'berikanTugasGrup'])->name('penugasan.berikan-tugas-grup');

    Route::apiResource('penugasan', PenugasanController::class);

    Route::prefix('penugasan/{id}')->group(function () {
        Route::post('/terima', [PenugasanController::class, 'terima'])->name('penugasan.terima');
        Route::post('/tolak', [PenugasanController::class, 'tolak'])->name('penugasan.tolak');
        Route::post('/approve-mandiri', [PenugasanController::class, 'approveMandiri'])->name('penugasan.approve-mandiri');
        Route::post('/reject-mandiri', [PenugasanController::class, 'rejectMandiri'])->name('penugasan.reject-mandiri');
        Route::post('/submit', [PenugasanController::class, 'submit'])->name('penugasan.submit');
        Route::post('/nilai', [PenugasanController::class, 'nilai'])->name('penugasan.nilai');
        Route::post('/revisi', [PenugasanController::class, 'revisi'])->name('penugasan.revisi');
        Route::post('/progress', [PenugasanController::class, 'updateProgress'])->name('penugasan.progress');

        Route::get('/perpanjangan-waktu', [PenugasanController::class, 'riwayatPerpanjangan'])->name('penugasan.perpanjangan-waktu.index');
        Route::post('/perpanjangan-waktu', [PenugasanController::class, 'ajukanPerpanjangan'])->name('penugasan.perpanjangan-waktu.ajukan');
        Route::post('/perpanjangan-waktu/{perpanjanganId}/setujui', [PenugasanController::class, 'setujuiPerpanjangan'])->name('penugasan.perpanjangan-waktu.setujui');
        Route::post('/perpanjangan-waktu/{perpanjanganId}/tolak', [PenugasanController::class, 'tolakPerpanjangan'])->name('penugasan.perpanjangan-waktu.tolak');
    });
});

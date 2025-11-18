<?php

use Illuminate\Support\Facades\Route;
use Modules\Penugasan\Http\Controllers\PenugasanController;
use Modules\Penugasan\Http\Controllers\TugasPokokController;
use Modules\Penugasan\Http\Controllers\TugasHarianController;
use Modules\Penugasan\Http\Controllers\TugasTambahanController;

/*
|--------------------------------------------------------------------------
| Web Routes - URUTAN SANGAT PENTING!
| Route SPESIFIK harus di ATAS route UMUM
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('penugasan')->name('penugasan.')->group(function () {

    // ============================================
    // PENUGASAN GENERAL ROUTES
    // ============================================
    Route::get('/', [PenugasanController::class, 'index'])->name('index');
    Route::get('/{id}', [PenugasanController::class, 'show'])->name('show');
    Route::post('/berikan-tugas', [PenugasanController::class, 'berikanTugas'])->name('berikan-tugas');
    Route::post('/buat-tugas', [PenugasanController::class, 'buatTugas'])->name('buat-tugas');
    Route::post('/upload-bukti', [PenugasanController::class, 'uploadBukti'])->name('upload-bukti');
    Route::post('/validasi-tugas/{id}', [PenugasanController::class, 'validasiTugas'])->name('validasi-tugas');
    Route::post('/preview-penilaian', [PenugasanController::class, 'previewPenilaian'])->name('preview-penilaian');
    Route::post('/catatan-monitoring', [PenugasanController::class, 'catatanMonitoring'])->name('catatan-monitoring');
    Route::get('/dashboard-monitoring', [PenugasanController::class, 'dashboardMonitoring'])->name('dashboard-monitoring');

    // ============================================
    // TUGAS POKOK ROUTES - /tugas-pokok
    // ============================================
    Route::prefix('tugas-pokok')->name('tugas-pokok.')->group(function () {
        Route::post('/sinkron', [TugasPokokController::class, 'sinkronData'])->name('sinkron');
        Route::post('/{id}/update-status', [TugasPokokController::class, 'updateStatus'])->name('update-status');
    });

    // ============================================
    // TUGAS HARIAN ROUTES - /tugas-harian
    // ============================================
    Route::prefix('tugas-harian')->name('tugas-harian.')->group(function () {
        Route::get('/', [TugasHarianController::class, 'index'])->name('index');
        Route::get('/{id}/detail', [TugasHarianController::class, 'detail'])->name('detail');
        Route::get('/{id}/upload-eviden', [TugasHarianController::class, 'uploadEviden'])->name('upload-eviden');
        Route::get('/{id}/edit', [TugasHarianController::class, 'edit'])->name('edit');
        Route::get('/{id}/history', [TugasHarianController::class, 'getHistory'])->name('history');
        Route::put('/{id}', [TugasHarianController::class, 'update'])->name('update');
        Route::delete('/{id}', [TugasHarianController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/update-status', [TugasHarianController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/update-progress', [TugasHarianController::class, 'updateProgress'])->name('update-progress');
    });

    // ============================================
    // TUGAS TAMBAHAN ROUTES - /tugas-tambahan
    // ============================================
    Route::prefix('tugas-tambahan')->name('tugas-tambahan.')->group(function () {
        Route::get('/', [TugasTambahanController::class, 'index'])->name('index');
        Route::get('/{id}/upload-eviden', [TugasTambahanController::class, 'uploadEviden'])->name('upload-eviden');
        Route::get('/{id}/edit', [TugasTambahanController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TugasTambahanController::class, 'update'])->name('update');
        Route::delete('/{id}', [TugasTambahanController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/update-status', [TugasTambahanController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/update-progress', [TugasTambahanController::class, 'updateProgress'])->name('update-progress');
    });
});

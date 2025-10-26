<?php

use Illuminate\Support\Facades\Route;
use Modules\Penugasan\Http\Controllers\PenugasanController;
use Modules\Penugasan\Http\Controllers\TugasPokokController;
use Modules\Penugasan\Http\Controllers\TugasHarianController;

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
    Route::post('/berikan-tugas', [PenugasanController::class, 'berikanTugas'])->name('berikan-tugas');

    // ============================================
    // TUGAS POKOK ROUTES - /tugas-pokok
    // ============================================
    Route::prefix('tugas-pokok')->name('tugas-pokok.')->group(function () {
        Route::get('/', [TugasPokokController::class, 'index'])->name('index');
        Route::post('/sinkron', [TugasPokokController::class, 'sinkronData'])->name('sinkron');
        Route::get('/{id}', [TugasPokokController::class, 'show'])->name('show');
        Route::post('/{id}/update-status', [TugasPokokController::class, 'updateStatus'])->name('update-status');
    });

    // ============================================
    // TUGAS HARIAN ROUTES - /tugas-harian
    // ============================================
    Route::prefix('tugas-harian')->name('tugas-harian.')->group(function () {
        Route::get('/{id}/edit', [TugasHarianController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TugasHarianController::class, 'update'])->name('update');
        Route::delete('/{id}', [TugasHarianController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/update-status', [TugasHarianController::class, 'updateStatus'])->name('update-status');
    });
});

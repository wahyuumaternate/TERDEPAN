<?php

use Illuminate\Support\Facades\Route;
// use Modules\Penugasan\Http\Controllers\PenugasanController;
use Modules\Penugasan\Http\Controllers\TugasPokokController;

/*
|--------------------------------------------------------------------------
| Web Routes - URUTAN SANGAT PENTING!
| Route SPESIFIK harus di ATAS route UMUM
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('penugasan')->name('penugasan.')->group(function () {

    // ============================================
    // TUGAS POKOK ROUTES - /tugas-pokok
    // ============================================
    Route::prefix('tugas-pokok')->name('tugas-pokok.')->group(function () {
        Route::get('/', [TugasPokokController::class, 'index'])->name('index');
        Route::post('/sinkron', [TugasPokokController::class, 'sinkronData'])->name('sinkron');
        Route::get('/{id}', [TugasPokokController::class, 'show'])->name('show');
        Route::post('/{id}/update-status', [TugasPokokController::class, 'updateStatus'])->name('update-status');
    });
});

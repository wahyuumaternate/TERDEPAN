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
        Route::get('/{id}', [TugasPokokController::class, 'show'])->name('show');
        Route::get('/edit/{id}', [TugasPokokController::class, 'edit'])->name('edit');
        Route::get('/create', [TugasPokokController::class, 'create'])->name('create');
        Route::post('/store', [TugasPokokController::class, 'store'])->name('store');
        Route::put('/update/{id}', [TugasPokokController::class, 'update'])->name('update');
        Route::delete('/{id}', [TugasPokokController::class, 'destroy'])->name('destroy');
    });
});

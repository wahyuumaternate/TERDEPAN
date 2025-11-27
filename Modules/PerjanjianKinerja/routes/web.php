<?php

use Illuminate\Support\Facades\Route;
use Modules\PerjanjianKinerja\Http\Controllers\TemplateController;
use Modules\PerjanjianKinerja\Http\Controllers\PerjanjianKinerjaController;
use Modules\PerjanjianKinerja\Http\Controllers\SasaranController;
use Modules\PerjanjianKinerja\Http\Controllers\ProgramController;

/*
|--------------------------------------------------------------------------
| Web Routes - Perjanjian Kinerja Module
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth'])->group(function () {

    // Prefix untuk semua route perjanjian kinerja
    Route::prefix('perjanjian-kinerja')->name('perjanjian-kinerja.')->group(function () {

        // ====================================
        // PK SAYA - Accessible by all users
        // ====================================
        Route::get('/pk-saya', [PerjanjianKinerjaController::class, 'pkSaya'])->name('pk-saya');

        // ====================================
        // PERIODE ROUTES (Admin/Kaban/Sekban only)
        // ====================================
        Route::middleware(['can:manage-periode'])->prefix('periode')->name('periode.')->group(function () {
            Route::get('/', [\Modules\PerjanjianKinerja\Http\Controllers\PeriodeController::class, 'index'])->name('index');
            Route::get('/create', [\Modules\PerjanjianKinerja\Http\Controllers\PeriodeController::class, 'create'])->name('create');
            Route::post('/', [\Modules\PerjanjianKinerja\Http\Controllers\PeriodeController::class, 'store'])->name('store');
            Route::get('/{id}', [\Modules\PerjanjianKinerja\Http\Controllers\PeriodeController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\Modules\PerjanjianKinerja\Http\Controllers\PeriodeController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\Modules\PerjanjianKinerja\Http\Controllers\PeriodeController::class, 'update'])->name('update');
            Route::delete('/{id}', [\Modules\PerjanjianKinerja\Http\Controllers\PeriodeController::class, 'destroy'])->name('destroy');

            // Actions
            Route::post('/{id}/buka', [\Modules\PerjanjianKinerja\Http\Controllers\PeriodeController::class, 'buka'])->name('buka');
            Route::post('/{id}/tutup', [\Modules\PerjanjianKinerja\Http\Controllers\PeriodeController::class, 'tutup'])->name('tutup');
        });

        // API: Get periode aktif
        Route::get('/api/periode-aktif', [\Modules\PerjanjianKinerja\Http\Controllers\PeriodeController::class, 'getPeriodeAktif'])->name('api.periode-aktif');

        // ====================================
        // TEMPLATE ROUTES
        // ====================================
        Route::prefix('template')->name('template.')->group(function () {
            Route::get('/', [TemplateController::class, 'index'])->name('index');
            Route::get('/create', [TemplateController::class, 'create'])->name('create');
            Route::post('/', [TemplateController::class, 'store'])->name('store');
            Route::get('/{id}', [TemplateController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [TemplateController::class, 'edit'])->name('edit');
            Route::put('/{id}', [TemplateController::class, 'update'])->name('update');
            Route::delete('/{id}', [TemplateController::class, 'destroy'])->name('destroy');

            // Template Actions
            Route::post('/{id}/activate', [TemplateController::class, 'activate'])->name('activate');
            Route::post('/{id}/duplicate', [TemplateController::class, 'duplicate'])->name('duplicate');

            // Template PDF
            Route::get('/{id}/preview-pdf', [TemplateController::class, 'previewPdf'])->name('preview-pdf');
            Route::get('/{id}/download-pdf', [TemplateController::class, 'downloadPdf'])->name('download-pdf');
        });
        // ====================================
        // VALIDASI ROUTES - Must be before /{id} routes
        // ====================================
        Route::get('/validasi', [PerjanjianKinerjaController::class, 'daftarValidasi'])->name('daftar-validasi');

        // ====================================
        // AJAX & API ROUTES - Must be before /{id} routes
        // ====================================
        Route::get('/get-atasan/{pegawaiId}', [PerjanjianKinerjaController::class, 'getAtasan'])->name('get-atasan');

        // ====================================
        // RESOURCE ROUTES
        // ====================================
        Route::get('/', [PerjanjianKinerjaController::class, 'index'])->name('index');
        Route::get('/create', [PerjanjianKinerjaController::class, 'create'])->name('create');
        Route::post('/', [PerjanjianKinerjaController::class, 'store'])->name('store');
        Route::get('/{id}', [PerjanjianKinerjaController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PerjanjianKinerjaController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PerjanjianKinerjaController::class, 'update'])->name('update');
        Route::delete('/{id}', [PerjanjianKinerjaController::class, 'destroy'])->name('destroy');

        // ====================================
        // PERJANJIAN KINERJA ACTIONS
        // ====================================
        Route::post('/{id}/validasi', [PerjanjianKinerjaController::class, 'validasi'])->name('validasi');
        Route::post('/{id}/generate', [PerjanjianKinerjaController::class, 'generatePDF'])->name('generate');
        Route::post('/{id}/sign', [PerjanjianKinerjaController::class, 'sign'])->name('sign');
        Route::post('/{id}/lock', [PerjanjianKinerjaController::class, 'lock'])->name('lock');
        Route::post('/{id}/unlock', [PerjanjianKinerjaController::class, 'unlock'])->name('unlock');
        Route::post('/{id}/calculate-anggaran', [PerjanjianKinerjaController::class, 'calculateAnggaran'])->name('calculate-anggaran');

        // ====================================
        // DOWNLOAD & PREVIEW
        // ====================================
        Route::get('/{id}/download', [PerjanjianKinerjaController::class, 'download'])->name('download');
        Route::get('/{id}/preview', [PerjanjianKinerjaController::class, 'preview'])->name('preview');

        // // ====================================
        // // SASARAN & INDIKATOR ROUTES
        // // ====================================
        // Route::prefix('sasaran')->name('sasaran.')->group(function () {
        //     Route::get('/', [SasaranController::class, 'index'])->name('index');
        //     Route::get('/create', [SasaranController::class, 'create'])->name('create');
        //     Route::post('/', [SasaranController::class, 'store'])->name('store');
        //     Route::get('/{id}', [SasaranController::class, 'show'])->name('show');
        //     Route::get('/{id}/edit', [SasaranController::class, 'edit'])->name('edit');
        //     Route::put('/{id}', [SasaranController::class, 'update'])->name('update');
        //     Route::delete('/{id}', [SasaranController::class, 'destroy'])->name('destroy');

        //     // Indikator nested routes
        //     Route::post('/{sasaranId}/indikator', [SasaranController::class, 'storeIndikator'])->name('indikator.store');
        //     Route::put('/indikator/{id}', [SasaranController::class, 'updateIndikator'])->name('indikator.update');
        //     Route::delete('/indikator/{id}', [SasaranController::class, 'destroyIndikator'])->name('indikator.destroy');
        // });

        // // ====================================
        // // PROGRAM & KEGIATAN ROUTES
        // // ====================================
        // Route::prefix('program')->name('program.')->group(function () {
        //     Route::get('/', [ProgramController::class, 'index'])->name('index');
        //     Route::get('/create', [ProgramController::class, 'create'])->name('create');
        //     Route::post('/', [ProgramController::class, 'store'])->name('store');
        //     Route::get('/{id}', [ProgramController::class, 'show'])->name('show');
        //     Route::get('/{id}/edit', [ProgramController::class, 'edit'])->name('edit');
        //     Route::put('/{id}', [ProgramController::class, 'update'])->name('update');
        //     Route::delete('/{id}', [ProgramController::class, 'destroy'])->name('destroy');

        //     // Kegiatan nested routes
        //     Route::post('/{programId}/kegiatan', [ProgramController::class, 'storeKegiatan'])->name('kegiatan.store');
        //     Route::put('/kegiatan/{id}', [ProgramController::class, 'updateKegiatan'])->name('kegiatan.update');
        //     Route::delete('/kegiatan/{id}', [ProgramController::class, 'destroyKegiatan'])->name('kegiatan.destroy');

        //     // Sub Kegiatan nested routes
        //     Route::post('/kegiatan/{kegiatanId}/sub-kegiatan', [ProgramController::class, 'storeSubKegiatan'])->name('sub-kegiatan.store');
        //     Route::put('/sub-kegiatan/{id}', [ProgramController::class, 'updateSubKegiatan'])->name('sub-kegiatan.update');
        //     Route::delete('/sub-kegiatan/{id}', [ProgramController::class, 'destroySubKegiatan'])->name('sub-kegiatan.destroy');
        // });
    });
});

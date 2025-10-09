<?php

use Illuminate\Support\Facades\Route;
use Modules\Dokumen\Http\Controllers\KategoriController;
use Modules\Dokumen\Http\Controllers\JenisController;
use Modules\Dokumen\Http\Controllers\FolderController;
use Modules\Dokumen\Http\Controllers\DokumenController;
use Modules\Dokumen\Http\Controllers\FileController;

/*
|--------------------------------------------------------------------------
| Web Routes - URUTAN SANGAT PENTING!
| Route SPESIFIK harus di ATAS route UMUM
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // ============================================
    // KATEGORI ROUTES - /dokumen/kategori
    // ============================================
    Route::prefix('dokumen/kategori')->name('dokumen.kategori.')->group(function () {
        Route::get('/', [KategoriController::class, 'index'])->name('index');
        Route::get('/create', [KategoriController::class, 'create'])->name('create');
        Route::post('/', [KategoriController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [KategoriController::class, 'edit'])->name('edit');
        Route::put('/{id}', [KategoriController::class, 'update'])->name('update');
        Route::get('/{id}', [KategoriController::class, 'show'])->name('show');
        Route::delete('/{id}', [KategoriController::class, 'destroy'])->name('destroy');
    });

    // ============================================
    // JENIS ROUTES - /dokumen/jenis
    // ============================================
    Route::prefix('dokumen/jenis')->name('dokumen.jenis.')->group(function () {
        Route::get('/', [JenisController::class, 'index'])->name('index');
        Route::get('/create', [JenisController::class, 'create'])->name('create');
        Route::post('/', [JenisController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [JenisController::class, 'edit'])->name('edit');
        Route::put('/{id}', [JenisController::class, 'update'])->name('update');
        Route::get('/{id}', [JenisController::class, 'show'])->name('show');
        Route::delete('/{id}', [JenisController::class, 'destroy'])->name('destroy');
    });

    // ============================================
    // FOLDER ROUTES - /dokumen/folder
    // ============================================
    Route::prefix('dokumen/folder')->name('dokumen.folder.')->group(function () {
        Route::get('/', [FolderController::class, 'index'])->name('index');
        Route::get('/create', [FolderController::class, 'create'])->name('create');
        Route::post('/', [FolderController::class, 'store'])->name('store');
        Route::get('/{id}/children', [FolderController::class, 'getChildren'])->name('children');
        Route::get('/{id}/edit', [FolderController::class, 'edit'])->name('edit');
        Route::put('/{id}', [FolderController::class, 'update'])->name('update');
        Route::get('/{id}', [FolderController::class, 'show'])->name('show');
        Route::delete('/{id}', [FolderController::class, 'destroy'])->name('destroy');
    });

    // ============================================
    // FILE ROUTES - /dokumen/file
    // ============================================
    Route::prefix('dokumen/file')->name('dokumen.file.')->group(function () {
        Route::get('/', [FileController::class, 'index'])->name('index');
        Route::get('/create', [FileController::class, 'create'])->name('create');
        Route::post('/', [FileController::class, 'store'])->name('store');
        Route::get('/dokumen/{dokumenId}/versions', [FileController::class, 'getVersions'])->name('versions');
        Route::get('/{id}/edit', [FileController::class, 'edit'])->name('edit');
        Route::put('/{id}', [FileController::class, 'update'])->name('update');
        Route::get('/{id}', [FileController::class, 'show'])->name('show');
        Route::delete('/{id}', [FileController::class, 'destroy'])->name('destroy');
    });

    // ============================================
    // API ROUTES - /dokumen/api/*
    // ============================================
    Route::get('/dokumen/api/folders', [DokumenController::class, 'getFolders'])->name('dokumen.folders');
    Route::get('/dokumen/api/jenis', [DokumenController::class, 'getJenis'])->name('dokumen.jenis');

    // ============================================
    // DOKUMEN ROUTES - /dokumen
    // HARUS DI PALING BAWAH!
    // ============================================
    Route::prefix('dokumen')->name('dokumen.')->group(function () {
        Route::get('/', [DokumenController::class, 'index'])->name('index');
        Route::post('/', [DokumenController::class, 'store'])->name('store');

        // Route dengan constraint - hanya terima angka
        Route::get('/{id}/edit', [DokumenController::class, 'edit'])
            ->name('edit')
            ->where('id', '[0-9]+');
        Route::get('/{id}/download', [DokumenController::class, 'download'])
            ->name('download')
            ->where('id', '[0-9]+');
        Route::put('/{id}', [DokumenController::class, 'update'])
            ->name('update')
            ->where('id', '[0-9]+');
        Route::get('/{id}', [DokumenController::class, 'show'])
            ->name('show')
            ->where('id', '[0-9]+');
        Route::delete('/{id}', [DokumenController::class, 'destroy'])
            ->name('destroy')
            ->where('id', '[0-9]+');
    });
});

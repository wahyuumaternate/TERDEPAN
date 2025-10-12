<?php

use Illuminate\Support\Facades\Route;
use Modules\Dokumen\Http\Controllers\DocumentHistoryController;
use Modules\Dokumen\Http\Controllers\KategoriController;
use Modules\Dokumen\Http\Controllers\JenisController;
use Modules\Dokumen\Http\Controllers\FolderController;
use Modules\Dokumen\Http\Controllers\DokumenController;
use Modules\Dokumen\Http\Controllers\FileController;
use Modules\Dokumen\Http\Controllers\TemplateController;

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
        // Get all folders (with AJAX support)
        Route::get('/', [FolderController::class, 'index'])->name('index');

        // Get bidang list
        Route::get('/bidang', [FolderController::class, 'getBidang'])->name('bidang');

        // Get folder tree
        Route::get('/tree', [FolderController::class, 'getTree'])->name('tree');

        // Create new folder
        Route::get('/create', [FolderController::class, 'create'])->name('create');
        Route::post('/', [FolderController::class, 'store'])->name('store');

        // Get folder details
        Route::get('/{id}', [FolderController::class, 'show'])->name('show');

        // Get folder children
        Route::get('/{id}/children', [FolderController::class, 'getChildren'])->name('children');

        // Edit folder
        Route::get('/{id}/edit', [FolderController::class, 'edit'])->name('edit');
        Route::put('/{id}', [FolderController::class, 'update'])->name('update');

        // Delete folder
        Route::delete('/{id}', [FolderController::class, 'destroy'])->name('destroy');

        // Get folder files
        Route::get('/{id}/files', [FolderController::class, 'getFiles'])->name('files');
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
    // Route untuk get bidang - PRIMARY
    Route::get('/dokumen/get-bidang', [DokumenController::class, 'getBidang'])->name('dokumen.get.bidang');
    Route::get('dokumen/folder/{id}/children', 'Modules\Dokumen\Http\Controllers\FolderController@getChildren')->name('dokumen.folder.children');
    // Route untuk master bidang - FALLBACK 1
    Route::get('/master/bidang', [DokumenController::class, 'getBidang'])->name('master.bidang.index');
    Route::post('/preview-nomor', [DokumenController::class, 'previewNomor'])->name('preview-nomor');
    // ============================================
    // DOKUMEN ROUTES - /dokumen
    // HARUS DI PALING BAWAH!
    Route::get('dokumen/folder/{id}/dokumen', 'Modules\Dokumen\Http\Controllers\FolderController@getFolderDokumen')
        ->name('dokumen.folder.dokumen');
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

    // Template Routes
    Route::prefix('dokumen/template')->name('dokumen.template.')->middleware(['auth'])->group(function () {
        Route::get('/', [TemplateController::class, 'index'])->name('index');
        Route::post('/', [TemplateController::class, 'store'])->name('store');
        Route::get('/{id}', [TemplateController::class, 'show'])->name('show');
        Route::put('/{id}', [TemplateController::class, 'update'])->name('update');
        Route::delete('/{id}', [TemplateController::class, 'destroy'])->name('destroy');

        // Additional routes
        Route::get('/variables/list', [TemplateController::class, 'getVariables'])->name('variables');
        Route::post('/{id}/preview', [TemplateController::class, 'preview'])->name('preview');
        Route::post('/{id}/generate', [TemplateController::class, 'generate'])->name('generate');
        Route::get('/{id}/edit', [TemplateController::class, 'edit'])->name('edit');
    });


    // Document History - User View
    Route::prefix('dokumen')->name('dokumen.')->group(function () {

        // History Page
        Route::get('/history', [DocumentHistoryController::class, 'index'])
            ->name('history.index');

        // Get User Documents (AJAX)
        Route::get('/history/data', [DocumentHistoryController::class, 'getUserDocuments'])
            ->name('history.data');

        // Get Statistics
        Route::get('/history/statistics', [DocumentHistoryController::class, 'getStatistics'])
            ->name('history.statistics');

        // Generated Document Actions
        Route::prefix('generated')->name('generated.')->group(function () {
            // View specific document
            Route::get('/{id}', [DocumentHistoryController::class, 'show'])
                ->name('show');

            // Download document
            Route::get('/{id}/download', [DocumentHistoryController::class, 'download'])
                ->name('download');

            // Delete document
            Route::delete('/{id}', [DocumentHistoryController::class, 'destroy'])
                ->name('destroy');
        });
    });
});

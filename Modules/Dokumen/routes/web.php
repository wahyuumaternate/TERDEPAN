<?php

use Illuminate\Support\Facades\Route;
use Modules\Dokumen\Http\Controllers\DocumentHistoryController;
use Modules\Dokumen\Http\Controllers\FolderController;
use Modules\Dokumen\Http\Controllers\DokumenController;
use Modules\Dokumen\Http\Controllers\FileController;
use Modules\Dokumen\Http\Controllers\TemplateController;

/*
|--------------------------------------------------------------------------
| Web Routes - Dokumen Module
|--------------------------------------------------------------------------
| Semua route untuk module Dokumen dengan prefix /dokumen
| Urutan route: spesifik -> umum (penting untuk mencegah konflik)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('dokumen')->name('dokumen.')->group(function () {

    // ============================================
    // FOLDER MANAGEMENT
    // ============================================
    Route::prefix('folder')->name('folder.')->group(function () {
        Route::get('/', [FolderController::class, 'index'])->name('index');
        Route::get('/bidang', [FolderController::class, 'getBidang'])->name('bidang');
        Route::get('/tree', [FolderController::class, 'getTree'])->name('tree');
        Route::get('/create', [FolderController::class, 'create'])->name('create');
        Route::post('/', [FolderController::class, 'store'])->name('store');

        // Route spesifik dengan ID
        Route::get('/{id}', [FolderController::class, 'show'])->name('show')->where('id', '[0-9]+');
        Route::get('/{id}/children', [FolderController::class, 'getChildren'])->name('children')->where('id', '[0-9]+');
        Route::get('/{id}/files', [FolderController::class, 'getFiles'])->name('files')->where('id', '[0-9]+');
        Route::get('/{id}/dokumen', [FolderController::class, 'getFolderDokumen'])->name('dokumen')->where('id', '[0-9]+');
        Route::get('/{id}/edit', [FolderController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
        Route::put('/{id}', [FolderController::class, 'update'])->name('update')->where('id', '[0-9]+');
        Route::delete('/{id}', [FolderController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
    });

    // ============================================
    // FILE MANAGEMENT
    // ============================================
    Route::prefix('file')->name('file.')->group(function () {
        Route::get('/', [FileController::class, 'index'])->name('index');
        Route::get('/create', [FileController::class, 'create'])->name('create');
        Route::post('/', [FileController::class, 'store'])->name('store');
        Route::get('/dokumen/{dokumenId}/versions', [FileController::class, 'getVersions'])->name('versions');

        Route::get('/{id}', [FileController::class, 'show'])->name('show')->where('id', '[0-9]+');
        Route::get('/{id}/edit', [FileController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
        Route::put('/{id}', [FileController::class, 'update'])->name('update')->where('id', '[0-9]+');
        Route::delete('/{id}', [FileController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
    });

    // ============================================
    // TEMPLATE MANAGEMENT
    // ============================================
    Route::prefix('template')->name('template.')->group(function () {
        Route::get('/', [TemplateController::class, 'index'])->name('index');
        Route::get('/variables/list', [TemplateController::class, 'getVariables'])->name('variables');
        Route::post('/', [TemplateController::class, 'store'])->name('store');

        Route::get('/{id}', [TemplateController::class, 'show'])->name('show')->where('id', '[0-9]+');
        Route::get('/{id}/edit', [TemplateController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
        Route::post('/{id}/preview', [TemplateController::class, 'preview'])->name('preview')->where('id', '[0-9]+');
        Route::post('/{id}/generate', [TemplateController::class, 'generate'])->name('generate')->where('id', '[0-9]+');
        Route::put('/{id}', [TemplateController::class, 'update'])->name('update')->where('id', '[0-9]+');
        Route::delete('/{id}', [TemplateController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
    });

    // ============================================
    // DOCUMENT HISTORY
    // ============================================
    Route::prefix('history')->name('history.')->group(function () {
        Route::get('/', [DocumentHistoryController::class, 'index'])->name('index');
        Route::get('/data', [DocumentHistoryController::class, 'getUserDocuments'])->name('data');
        Route::get('/statistics', [DocumentHistoryController::class, 'getStatistics'])->name('statistics');
    });

    Route::prefix('generated')->name('generated.')->group(function () {
        Route::get('/{id}', [DocumentHistoryController::class, 'show'])->name('show')->where('id', '[0-9]+');
        Route::get('/{id}/download', [DocumentHistoryController::class, 'download'])->name('download')->where('id', '[0-9]+');
        Route::delete('/{id}', [DocumentHistoryController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
    });

    // ============================================
    // API ENDPOINTS
    // ============================================
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/folders', [DokumenController::class, 'getFolders'])->name('folders');
    });

    // ============================================
    // DOKUMEN CRUD (Main Resource)
    // ============================================
    Route::get('/', [DokumenController::class, 'index'])->name('index');
    Route::get('/get-bidang', [DokumenController::class, 'getBidang'])->name('get.bidang');
    Route::post('/', [DokumenController::class, 'store'])->name('store');

    Route::get('/{id}', [DokumenController::class, 'show'])->name('show')->where('id', '[0-9]+');
    Route::get('/{id}/edit', [DokumenController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
    Route::get('/{id}/download', [DokumenController::class, 'download'])->name('download')->where('id', '[0-9]+');
    Route::put('/{id}', [DokumenController::class, 'update'])->name('update')->where('id', '[0-9]+');
    Route::delete('/{id}', [DokumenController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
});

// ============================================
// FALLBACK ROUTES (Outside dokumen prefix)
// ============================================
Route::middleware(['auth'])->group(function () {
    // Master Bidang - Fallback route
    Route::get('/master/bidang', [DokumenController::class, 'getBidang'])->name('master.bidang.index');

    // Preview Nomor - Legacy route
    Route::post('/preview-nomor', [DokumenController::class, 'previewNomor'])->name('preview-nomor');
});

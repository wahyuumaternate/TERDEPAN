<?php

use Illuminate\Support\Facades\Route;
use Modules\TerminalData\Http\Controllers\Api\TdFileController;
use Modules\TerminalData\Http\Controllers\Api\TdFolderController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Route statis/aksi tambahan didaftarkan sebelum apiResource supaya tidak tertimpa
    // wildcard {folder} dari show()/update()/destroy().
    Route::prefix('folders/{folder}')->name('folders.')->group(function () {
        Route::get('/children', [TdFolderController::class, 'children'])->name('children');
        Route::get('/breadcrumb', [TdFolderController::class, 'breadcrumb'])->name('breadcrumb');
        Route::get('/stats', [TdFolderController::class, 'stats'])->name('stats');
        Route::post('/move', [TdFolderController::class, 'move'])->name('move');
        Route::post('/toggle-star', [TdFolderController::class, 'toggleStar'])->name('toggle-star');
        Route::post('/restore', [TdFolderController::class, 'restore'])->name('restore');
        Route::delete('/force-delete', [TdFolderController::class, 'forceDelete'])->name('force-delete');
    });

    Route::apiResource('folders', TdFolderController::class)->names('folders');

    Route::prefix('files')->name('files.')->group(function () {
        Route::post('/upload', [TdFileController::class, 'upload'])->name('upload');
        Route::get('/search', [TdFileController::class, 'search'])->name('search');
        Route::get('/{file}/download', [TdFileController::class, 'download'])->name('download');
        Route::get('/{file}/serve', [TdFileController::class, 'serve'])->name('serve');
        Route::put('/{file}', [TdFileController::class, 'update'])->name('update');
        Route::delete('/{file}', [TdFileController::class, 'destroy'])->name('destroy');
        Route::post('/{file}/restore', [TdFileController::class, 'restore'])->name('restore');
        Route::delete('/{file}/force-delete', [TdFileController::class, 'forceDelete'])->name('force-delete');
    });

    Route::post('trash/empty', [TdFileController::class, 'emptyTrash'])->name('trash.empty');
});

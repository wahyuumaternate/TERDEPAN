<?php

use Illuminate\Support\Facades\Route;
use Modules\TerminalData\Http\Controllers\Api\TdFileController;
use Modules\TerminalData\Http\Controllers\Api\TdFolderController;
use Modules\TerminalData\Http\Controllers\TerminalDataController;

Route::middleware(['auth', 'verified', 'must_change_password'])->group(function () {
    Route::prefix('terminal-data')->name('terminaldata.')->group(function () {
        Route::get('/', TerminalDataController::class.'@index')->name('index');
        Route::get('/folders', [TerminalDataController::class, 'folderIndex'])->name('folders.index');
        Route::get('/folder/{folder}', [TerminalDataController::class, 'folderDetail'])->name('folder.detail');
        Route::get('/sampah', [TerminalDataController::class, 'sampahIndex'])->name('sampah.index');

        // API Routes for folders
        Route::get('/api/folders', [TdFolderController::class, 'index'])->name('foldersData.index');
        Route::post('/api/folders', [TdFolderController::class, 'store'])->name('foldersData.store');
        Route::get('/api/folders/{folder}', [TdFolderController::class, 'show'])->name('foldersData.show');
        Route::put('/api/folders/{folder}', [TdFolderController::class, 'update'])->name('foldersData.update');
        Route::get('/api/folders/{folder}/children', [TdFolderController::class, 'children'])->name('foldersData.children');
        Route::get('/api/folders/{folder}/detail', [TdFolderController::class, 'detail'])->name('foldersData.detail');
        Route::delete('/api/folders/{folder}', [TdFolderController::class, 'destroy'])->name('foldersData.destroy');
        Route::post('/api/folders/{folder}/restore', [TdFolderController::class, 'restore'])->name('foldersData.restore');
        Route::delete('/api/folders/{folder}/force-delete', [TdFolderController::class, 'forceDelete'])->name('foldersData.forceDelete');

        // API Routes for files
        Route::post('/api/files/upload', [TdFileController::class, 'upload'])->name('filesData.upload');
        Route::get('/api/files/search', [TdFileController::class, 'search'])->name('filesData.search');
        Route::get('/api/files/{file}/download', [TdFileController::class, 'download'])->name('filesData.download');
        Route::get('/api/files/{file}/serve', [TdFileController::class, 'serve'])->name('filesData.serve');
        Route::get('/api/files/{file}/detail', [TdFileController::class, 'detail'])->name('filesData.detail');
        Route::put('/api/files/{file}', [TdFileController::class, 'update'])->name('filesData.update');
        Route::delete('/api/files/{file}', [TdFileController::class, 'destroy'])->name('filesData.destroy');
        Route::post('/api/files/{file}/restore', [TdFileController::class, 'restore'])->name('filesData.restore');
        Route::delete('/api/files/{file}/force-delete', [TdFileController::class, 'forceDelete'])->name('filesData.forceDelete');

        // API Route for trash operations
        Route::post('/api/trash/empty', [TdFileController::class, 'emptyTrash'])->name('trash.empty');
    });
    // Route::resource('terminal-data', TerminalDataController::class)->names('terminaldata');
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\TerminalData\Http\Controllers\Api\TdFolderController;
use Modules\TerminalData\Http\Controllers\TerminalDataController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('terminal-data')->name('terminaldata.')->group(function () {
        Route::get('/', TerminalDataController::class . '@index')->name('index');
        Route::get('/folders', [TerminalDataController::class, 'folderIndex'])->name('folders.index');
        Route::get('/folder/{folder}', [TerminalDataController::class, 'folderDetail'])->name('folder.detail');

        // API Routes for folders
        Route::get('/api/folders', [TdFolderController::class, 'index'])->name('foldersData.index');
        Route::get('/api/folders/{folder}', [TdFolderController::class, 'show'])->name('foldersData.show');
        Route::get('/api/folders/{folder}/children', [TdFolderController::class, 'children'])->name('foldersData.children');
    });
    // Route::resource('terminal-data', TerminalDataController::class)->names('terminaldata');
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\TerminalData\Http\Controllers\Api\TdFolderController;
use Modules\TerminalData\Http\Controllers\TerminalDataController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('terminal-data')->name('terminaldata.')->group(function () {
        Route::get('/', TerminalDataController::class . '@index')->name('index');
        Route::get('/folders', [TerminalDataController::class, 'folderIndex'])->name('folders.index');
        Route::get('/api/folders', [TdFolderController::class, 'index'])->name('foldersData.index');
    });
    // Route::resource('terminal-data', TerminalDataController::class)->names('terminaldata');
});

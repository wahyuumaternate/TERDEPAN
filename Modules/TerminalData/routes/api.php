<?php

use Illuminate\Support\Facades\Route;
use Modules\TerminalData\Http\Controllers\Api\TdFolderController;
use Modules\TerminalData\Http\Controllers\TerminalDataController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('folders', TdFolderController::class)->names('folders');
});

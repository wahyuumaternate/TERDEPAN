<?php

use Illuminate\Support\Facades\Route;
use Modules\TerminalData\Http\Controllers\TerminalDataController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('terminaldatas', TerminalDataController::class)->names('terminaldata');
});

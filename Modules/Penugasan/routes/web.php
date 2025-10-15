<?php

use Illuminate\Support\Facades\Route;
use Modules\Penugasan\Http\Controllers\PenugasanController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('penugasans', PenugasanController::class)->names('penugasan');
});

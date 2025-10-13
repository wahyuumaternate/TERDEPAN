<?php

use Illuminate\Support\Facades\Route;
use Modules\PerjanjianKinerja\Http\Controllers\PerjanjianKinerjaController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('perjanjiankinerjas', PerjanjianKinerjaController::class)->names('perjanjiankinerja');
});

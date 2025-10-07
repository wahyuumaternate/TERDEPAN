<?php

use Illuminate\Support\Facades\Route;
use Modules\Pelatihan\Http\Controllers\PelatihanController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('pelatihans', PelatihanController::class)->names('pelatihan');
});

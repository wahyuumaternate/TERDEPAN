<?php

use Illuminate\Support\Facades\Route;
use Modules\PerjanjianKinerja\Http\Controllers\PerjanjianKinerjaController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('perjanjiankinerjas', PerjanjianKinerjaController::class)->names('perjanjiankinerja');
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\CoreController;
use App\Http\Controllers\Api\MasterPegawaiController;
use App\Http\Controllers\Api\MasterJabatanController;
use App\Http\Controllers\Api\MasterBidangController;
use App\Http\Controllers\Api\MasterTtdDigitalController;
use App\Http\Controllers\Api\AuthController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Route::apiResource('cores', CoreController::class)->names('core');
    Route::apiResource('master-pegawai', MasterPegawaiController::class);
    Route::apiResource('master-jabatan', MasterJabatanController::class);
    Route::apiResource('master-bidang', MasterBidangController::class);
    Route::apiResource('master-ttd-digital', MasterTtdDigitalController::class);
});

Route::post('v1/login', [AuthController::class, 'login']);

<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MasterBidangController;
use App\Http\Controllers\Api\MasterJabatanController;
use App\Http\Controllers\Api\MasterSubBidangController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('master-jabatan', MasterJabatanController::class);
    Route::apiResource('master-bidang', MasterBidangController::class);
    Route::apiResource('master-sub-bidang', MasterSubBidangController::class);
    Route::delete('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});

Route::post('v1/login', [AuthController::class, 'login']);

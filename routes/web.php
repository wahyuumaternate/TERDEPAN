<?php

use App\Http\Controllers\Master\MasterPegawaiController;
use App\Http\Controllers\Master\MasterBidangController;
use App\Http\Controllers\Master\MasterJabatanController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/e-kinerja', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('e-kinerja.index');

Route::get('/', function () {
    return view('welcome');
})->middleware(['auth', 'verified'])->name('welcome');

Route::middleware('auth')->prefix('master')->name('master.')->group(function () {
    // ============================
    // MASTER PEGAWAI ROUTES - /master/pegawai
    // ============================
    Route::resource('pegawai', MasterPegawaiController::class)->names([
        'index' => 'pegawai.index',
        'create' => 'pegawai.create',
        'store' => 'pegawai.store',
        'show' => 'pegawai.show',
        'edit' => 'pegawai.edit',
        'update' => 'pegawai.update',
        'destroy' => 'pegawai.destroy',
    ]);

    // ============================
    // MASTER BIDANG ROUTES - /master/bidang
    // ============================
    Route::resource('bidang', MasterBidangController::class)->names([
        'index' => 'bidang.index',
        'create' => 'bidang.create',
        'store' => 'bidang.store',
        'show' => 'bidang.show',
        'edit' => 'bidang.edit',
        'update' => 'bidang.update',
        'destroy' => 'bidang.destroy',
    ]);

    // ============================
    // MASTER JABATAN ROUTES - /master/jabatan
    // ============================
    Route::resource('jabatan', MasterJabatanController::class)->names([
        'index' => 'jabatan.index',
        'create' => 'jabatan.create',
        'store' => 'jabatan.store',
        'show' => 'jabatan.show',
        'edit' => 'jabatan.edit',
        'update' => 'jabatan.update',
        'destroy' => 'jabatan.destroy',
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

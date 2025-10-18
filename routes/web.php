<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->prefix('master')->name('master.')->group(function () {

    // ============================
    // MASTER PEGAWAI ROUTES - /master/pegawai
    // ============================
    Route::resource('pegawai', App\Http\Controllers\Master\MasterPegawaiController::class)->names([
        'index' => 'pegawai.index',
        'create' => 'pegawai.create', 
        'store' => 'pegawai.store',
        'show' => 'pegawai.show',
        'edit' => 'pegawai.edit',
        'update' => 'pegawai.update',
        'destroy' => 'pegawai.destroy',
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Master\MasterBidangController;
use App\Http\Controllers\Master\MasterJabatanController;
use App\Http\Controllers\Master\MasterPegawaiController;
use App\Http\Controllers\Master\MasterSubBidangController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PanduanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Testing\WhatsappTestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'must_change_password'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    // Alias lama, dipertahankan supaya link/bookmark yang ada tidak putus.
    Route::get('/e-kinerja', [DashboardController::class, 'index'])->name('e-kinerja.index');
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
});

Route::middleware(['auth', 'must_change_password'])->prefix('master')->name('master.')->group(function () {
    // ============================
    // MASTER PEGAWAI ROUTES - /master/pegawai
    // ============================
    // Rute statis (import, kirim-email-login) didaftarkan sebelum resource() supaya tidak
    // tertimpa wildcard GET pegawai/{pegawai} dari resource.show().
    Route::get('pegawai/import', [MasterPegawaiController::class, 'import'])->name('pegawai.import');
    Route::post('pegawai/import', [MasterPegawaiController::class, 'importStore'])->name('pegawai.import.store');
    Route::post('pegawai/kirim-email-login', [MasterPegawaiController::class, 'kirimEmailLogin'])->name('pegawai.kirim-email-login');

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
    // MASTER SUB BIDANG ROUTES - /master/sub-bidang
    // ============================
    Route::resource('sub-bidang', MasterSubBidangController::class)->names([
        'index' => 'sub-bidang.index',
        'create' => 'sub-bidang.create',
        'store' => 'sub-bidang.store',
        'show' => 'sub-bidang.show',
        'edit' => 'sub-bidang.edit',
        'update' => 'sub-bidang.update',
        'destroy' => 'sub-bidang.destroy',
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

Route::middleware(['auth', 'must_change_password'])->prefix('panduan')->name('panduan.')->group(function () {
    // Rute statis (create) didaftarkan sebelum wildcard {panduan} supaya tidak tertimpa.
    Route::get('/', [PanduanController::class, 'index'])->name('index');
    Route::get('/create', [PanduanController::class, 'create'])->name('create');
    Route::post('/', [PanduanController::class, 'store'])->name('store');
    Route::get('/{panduan}/preview', [PanduanController::class, 'preview'])->name('preview');
    Route::get('/{panduan}/download', [PanduanController::class, 'download'])->name('download');
    Route::get('/{panduan}/edit', [PanduanController::class, 'edit'])->name('edit');
    Route::put('/{panduan}', [PanduanController::class, 'update'])->name('update');
    Route::delete('/{panduan}', [PanduanController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth', 'must_change_password'])->prefix('testing')->name('testing.')->group(function () {
    Route::get('/whatsapp', [WhatsappTestController::class, 'index'])->name('whatsapp');
    Route::post('/whatsapp', [WhatsappTestController::class, 'send'])->name('whatsapp.send');
});

Route::middleware(['auth', 'must_change_password'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

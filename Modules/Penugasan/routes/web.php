<?php

use Illuminate\Support\Facades\Route;
use Modules\Penugasan\Http\Controllers\ApiController;
use Modules\Penugasan\Http\Controllers\DashboardController;
use Modules\Penugasan\Http\Controllers\PenugasanController;
use Modules\Penugasan\Http\Controllers\TeamController;

/*
|--------------------------------------------------------------------------
| Web Routes - Modul Penugasan
|--------------------------------------------------------------------------
| Tugas Pokok, Tugas Tambahan, dan Tugas Harian sudah digabung menjadi satu
| entitas Penugasan (kolom `jenis` sebagai label klasifikasi). Seluruh view
| Blade sudah disesuaikan penuh ke skema baru (lihat dok. 06 §6) sehingga
| alias route lama (tugas-pokok.*, tugas-harian.*, tugas-tambahan.*) sudah
| tidak diperlukan lagi dan telah dihapus.
|--------------------------------------------------------------------------
| PENTING: rute statis (tim/*, api/*, daftar, tugas-saya, create) harus
| didaftarkan SEBELUM rute wildcard `/{id}` di bawah, supaya tidak ter-shadow
| oleh wildcard tersebut (mis. GET /penugasan/tim salah tertangkap sebagai
| show($id = 'tim') jika wildcard didaftarkan lebih dulu).
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('penugasan')->name('penugasan.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ============================================
    // PENUGASAN (gabungan Tugas Pokok + Tugas Tambahan + Tugas Harian)
    // ============================================
    Route::get('/daftar', [PenugasanController::class, 'index'])->name('index');
    Route::get('/tugas-saya', [PenugasanController::class, 'tugasSaya'])->name('tugas-saya');
    Route::get('/create', [PenugasanController::class, 'create'])->name('create');
    Route::post('/', [PenugasanController::class, 'store'])->name('store');

    // ============================================
    // TEAM MANAGEMENT (Untuk Atasan)
    // ============================================
    Route::prefix('tim')->name('tim.')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->name('index');
        Route::get('/overview', [TeamController::class, 'overview'])->name('overview');
        Route::get('/anggota/{pegawai}', [TeamController::class, 'detailAnggota'])->name('detail-anggota');

        Route::get('/berikan-tugas', [TeamController::class, 'formBerikanTugas'])->name('form-berikan-tugas');
        Route::post('/berikan-tugas', [TeamController::class, 'berikanTugas'])->name('berikan-tugas');

        Route::get('/validasi', [TeamController::class, 'daftarValidasi'])->name('daftar-validasi');
        Route::post('/validasi/{tugas}', [TeamController::class, 'validasiTugas'])->name('validasi-tugas');
        Route::post('/preview-penilaian', [TeamController::class, 'previewPenilaian'])->name('preview-penilaian');

        Route::get('/monitoring', [TeamController::class, 'monitoring'])->name('monitoring');
        Route::post('/catatan-monitoring', [TeamController::class, 'catatanMonitoring'])->name('catatan-monitoring');
    });

    // ============================================
    // API ENDPOINTS (untuk AJAX calls)
    // ============================================
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/statistik', [ApiController::class, 'statistik'])->name('statistik');
        Route::get('/kalender', [ApiController::class, 'kalender'])->name('kalender');
        Route::get('/notifikasi', [ApiController::class, 'notifikasi'])->name('notifikasi');
        Route::get('/workload-tim', [ApiController::class, 'workloadTim'])->name('workload-tim');
        Route::get('/progress-anggota/{pegawai}', [ApiController::class, 'progressAnggota'])->name('progress-anggota');
        Route::get('/pegawai/{pegawai}/tugas-pokok', [ApiController::class, 'tugasPokokByPegawai'])->name('tugas-pokok-by-pegawai');
    });

    // ============================================
    // PENUGASAN — rute wildcard /{id} (harus PALING BAWAH, lihat catatan di atas)
    // ============================================
    Route::get('/{id}', [PenugasanController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [PenugasanController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PenugasanController::class, 'update'])->name('update');
    Route::delete('/{id}', [PenugasanController::class, 'destroy'])->name('destroy');

    Route::post('/{id}/terima', [PenugasanController::class, 'terima'])->name('terima');
    Route::post('/{id}/tolak', [PenugasanController::class, 'tolak'])->name('tolak');
    Route::post('/{id}/mulai', [PenugasanController::class, 'mulai'])->name('mulai');
    Route::post('/{id}/submit', [PenugasanController::class, 'submit'])->name('submit');
    Route::get('/{id}/upload-bukti', [PenugasanController::class, 'formUploadBukti'])->name('form-upload-bukti');
    Route::post('/{id}/upload-bukti', [PenugasanController::class, 'uploadBukti'])->name('upload-bukti');
    Route::post('/{id}/update-progress', [PenugasanController::class, 'updateProgress'])->name('update-progress');
    Route::get('/{id}/history', [PenugasanController::class, 'history'])->name('history');
});

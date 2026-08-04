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
| Struktur mengikuti docs/plan/08-rencana_implementasi_tampilan_web_penugasan.md:
| "Tugas Saya" dan "Tugas yang Saya Berikan" digabung jadi satu halaman
| (penugasan.tugas-saya) dengan query param ?tab=saya|diberikan (§4.1).
| Route lama form-berikan-tugas/daftar-validasi dipertahankan sebagai
| redirect supaya bookmark/link lama (termasuk dashboard.blade.php) tidak 404.
|--------------------------------------------------------------------------
| PENTING: rute statis (tim/*, api/*, daftar, tugas-saya, create) harus
| didaftarkan SEBELUM rute wildcard `/{id}` di bawah, supaya tidak ter-shadow
| oleh wildcard tersebut.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('penugasan')->name('penugasan.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ============================================
    // PENUGASAN (gabungan Tugas Pokok + Tugas Tambahan + Tugas Harian)
    // ============================================
    Route::get('/daftar', [PenugasanController::class, 'index'])->name('index');
    Route::get('/tugas-saya', [PenugasanController::class, 'tugasSaya'])->name('tugas-saya');
    Route::get('/tugas-saya/data', [PenugasanController::class, 'tugasSayaData'])->name('tugas-saya.data');
    Route::get('/create', [PenugasanController::class, 'create'])->name('create');
    Route::post('/', [PenugasanController::class, 'store'])->name('store');
    Route::post('/grup', [PenugasanController::class, 'storeGrup'])->name('store-grup');

    // ============================================
    // TEAM (sisa endpoint yang masih dipakai halaman detail tugas + redirect kompatibilitas lama)
    // ============================================
    Route::prefix('tim')->name('tim.')->group(function () {
        Route::post('/preview-penilaian', [TeamController::class, 'previewPenilaian'])->name('preview-penilaian');

        // Redirect kompatibilitas — halaman lama digabung ke tab "diberikan" (dok. 08 §4.1)
        Route::get('/berikan-tugas', fn () => redirect()->route('penugasan.tugas-saya', ['tab' => 'diberikan']))
            ->name('form-berikan-tugas');
        Route::get('/validasi', fn () => redirect()->route('penugasan.tugas-saya', ['tab' => 'diberikan']))
            ->name('daftar-validasi');
    });

    // ============================================
    // API ENDPOINTS (untuk AJAX calls) — lihat dok. 08 §8, sebagian orphan
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
    Route::put('/{id}', [PenugasanController::class, 'update'])->name('update');
    Route::delete('/{id}', [PenugasanController::class, 'destroy'])->name('destroy');

    Route::post('/{id}/terima', [PenugasanController::class, 'terima'])->name('terima');
    Route::post('/{id}/tolak', [PenugasanController::class, 'tolak'])->name('tolak');
    Route::post('/{id}/submit', [PenugasanController::class, 'submit'])->name('submit');
    Route::post('/{id}/nilai', [PenugasanController::class, 'nilai'])->name('nilai');
    Route::post('/{id}/revisi', [PenugasanController::class, 'revisi'])->name('revisi');
    Route::post('/{id}/approve-mandiri', [PenugasanController::class, 'approveMandiri'])->name('approve-mandiri');
    Route::post('/{id}/reject-mandiri', [PenugasanController::class, 'rejectMandiri'])->name('reject-mandiri');

    Route::post('/{id}/perpanjangan-waktu', [PenugasanController::class, 'ajukanPerpanjangan'])->name('perpanjangan-waktu.ajukan');
    Route::post('/{id}/perpanjangan-waktu/{perpanjanganId}/setujui', [PenugasanController::class, 'setujuiPerpanjangan'])->name('perpanjangan-waktu.setujui');
    Route::post('/{id}/perpanjangan-waktu/{perpanjanganId}/tolak', [PenugasanController::class, 'tolakPerpanjangan'])->name('perpanjangan-waktu.tolak');

    Route::get('/{id}/upload-bukti', [PenugasanController::class, 'formUploadBukti'])->name('form-upload-bukti');
    Route::post('/{id}/upload-bukti', [PenugasanController::class, 'uploadBukti'])->name('upload-bukti');
    Route::post('/{id}/update-progress', [PenugasanController::class, 'updateProgress'])->name('update-progress');
});

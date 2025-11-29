<?php

use Illuminate\Support\Facades\Route;
use Modules\Penugasan\Http\Controllers\DashboardController;
use Modules\Penugasan\Http\Controllers\PenugasanController;
use Modules\Penugasan\Http\Controllers\TugasPokokController;
use Modules\Penugasan\Http\Controllers\TugasHarianController;
use Modules\Penugasan\Http\Controllers\TugasTambahanController;
use Modules\Penugasan\Http\Controllers\TeamController;
use Modules\Penugasan\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| Web Routes - Modul Penugasan
|--------------------------------------------------------------------------
| Route structure mengikuti hierarki database dan best practice RESTful:
| - Dashboard (landing page)
| - Tugas Pokok (main tasks from PK)
| - Tugas Harian (daily tasks - child of Tugas Pokok)
| - Tugas Tambahan (additional tasks - standalone)
| - Team Management (for supervisors)
| - API endpoints (for AJAX)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('penugasan')->name('penugasan.')->group(function () {

    // ============================================
    // DASHBOARD
    // ============================================
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ============================================
    // TUGAS POKOK (Tugas utama dari Perjanjian Kinerja)
    // ============================================
    Route::prefix('tugas-pokok')->name('tugas-pokok.')->group(function () {
        // List & view
        Route::get('/', [TugasPokokController::class, 'index'])->name('index');
        Route::get('/saya', [TugasPokokController::class, 'tugasSaya'])->name('tugas-saya');
        Route::get('/{tugasPokok}', [TugasPokokController::class, 'show'])->name('show');

        // Actions
        Route::post('/sinkron', [TugasPokokController::class, 'sinkronData'])->name('sinkron');
        // Route::post('/{tugasPokok}/terima', [TugasPokokController::class, 'terima'])->name('terima');
        Route::post('/{tugasPokok}/update-status', [TugasPokokController::class, 'updateStatus'])->name('update-status');

        // Nested: Tugas Harian di bawah Tugas Pokok
        Route::prefix('{tugasPokok}/tugas-harian')->name('tugas-harian.')->group(function () {
            Route::get('/', [TugasHarianController::class, 'indexByTugasPokok'])->name('index');
            Route::post('/', [TugasHarianController::class, 'store'])->name('store');
        });
    });

    // ============================================
    // TUGAS HARIAN (Daily Tasks)
    // ============================================
    Route::prefix('tugas-harian')->name('tugas-harian.')->group(function () {
        // List & view
        Route::get('/', [TugasHarianController::class, 'index'])->name('index');
        Route::get('/saya', [TugasHarianController::class, 'tugasSaya'])->name('tugas-saya');
        Route::get('/buat', [TugasHarianController::class, 'create'])->name('create');
        Route::get('/{tugasHarian}', [TugasHarianController::class, 'show'])->name('show');
        Route::get('/{tugasHarian}/edit', [TugasHarianController::class, 'edit'])->name('edit');

        // CRUD operations
        Route::post('/', [TugasHarianController::class, 'store'])->name('store');
        Route::put('/{tugasHarian}', [TugasHarianController::class, 'update'])->name('update');
        Route::delete('/{tugasHarian}', [TugasHarianController::class, 'destroy'])->name('destroy');

        // Task actions
        Route::post('/{tugasHarian}/terima', [TugasHarianController::class, 'terima'])->name('terima');
        Route::post('/{tugasHarian}/tolak', [TugasHarianController::class, 'tolak'])->name('tolak');
        Route::post('/{tugasHarian}/mulai', [TugasHarianController::class, 'mulai'])->name('mulai');
        Route::post('/{tugasHarian}/submit', [TugasHarianController::class, 'submit'])->name('submit');
        Route::get('/{tugasHarian}/upload-bukti', [TugasHarianController::class, 'formUploadBukti'])->name('form-upload-bukti');
        Route::post('/{tugasHarian}/upload-bukti', [TugasHarianController::class, 'uploadBukti'])->name('upload-bukti');
        Route::post('/{tugasHarian}/update-progress', [TugasHarianController::class, 'updateProgress'])->name('update-progress');

        // History
        Route::get('/{tugasHarian}/history', [TugasHarianController::class, 'history'])->name('history');
    });

    // ============================================
    // TUGAS TAMBAHAN (Additional Tasks)
    // ============================================
    Route::prefix('tugas-tambahan')->name('tugas-tambahan.')->group(function () {
        // List & view
        Route::get('/', [TugasTambahanController::class, 'index'])->name('index');
        Route::get('/saya', [TugasTambahanController::class, 'tugasSaya'])->name('tugas-saya');
        Route::get('/create', [TugasTambahanController::class, 'create'])->name('create');
        Route::get('/{tugasTambahan}', [TugasTambahanController::class, 'show'])->name('show');
        Route::get('/{tugasTambahan}/edit', [TugasTambahanController::class, 'edit'])->name('edit');

        // CRUD operations
        Route::post('/', [TugasTambahanController::class, 'store'])->name('store');
        Route::put('/{tugasTambahan}', [TugasTambahanController::class, 'update'])->name('update');
        Route::delete('/{tugasTambahan}', [TugasTambahanController::class, 'destroy'])->name('destroy');

        // Task actions
        Route::post('/{tugasTambahan}/terima', [TugasTambahanController::class, 'terima'])->name('terima');
        Route::post('/{tugasTambahan}/tolak', [TugasTambahanController::class, 'tolak'])->name('tolak');
        Route::post('/{tugasTambahan}/mulai', [TugasTambahanController::class, 'mulai'])->name('mulai');
        Route::post('/{tugasTambahan}/submit', [TugasTambahanController::class, 'submit'])->name('submit');
        Route::post('/{tugasTambahan}/upload-bukti', [TugasTambahanController::class, 'uploadBukti'])->name('upload-bukti');
        Route::post('/{tugasTambahan}/update-progress', [TugasTambahanController::class, 'updateProgress'])->name('update-progress');
    });

    // ============================================
    // TEAM MANAGEMENT (Untuk Atasan)
    // ============================================
    Route::prefix('tim')->name('tim.')->group(function () {
        // Overview tim dan anggota
        Route::get('/', [TeamController::class, 'index'])->name('index');
        Route::get('/overview', [TeamController::class, 'overview'])->name('overview');
        Route::get('/anggota/{pegawai}', [TeamController::class, 'detailAnggota'])->name('detail-anggota');

        // Berikan tugas
        Route::get('/berikan-tugas', [TeamController::class, 'formBerikanTugas'])->name('form-berikan-tugas');
        Route::post('/berikan-tugas', [TeamController::class, 'berikanTugas'])->name('berikan-tugas');

        // Validasi tugas
        Route::get('/validasi', [TeamController::class, 'daftarValidasi'])->name('daftar-validasi');
        Route::post('/validasi/{tugas}', [TeamController::class, 'validasiTugas'])->name('validasi-tugas');
        Route::post('/preview-penilaian', [TeamController::class, 'previewPenilaian'])->name('preview-penilaian');

        // Monitoring
        Route::get('/monitoring', [TeamController::class, 'monitoring'])->name('monitoring');
        Route::post('/catatan-monitoring', [TeamController::class, 'catatanMonitoring'])->name('catatan-monitoring');
    });

    // ============================================
    // API ENDPOINTS (untuk AJAX calls)
    // ============================================
    Route::prefix('api')->name('api.')->group(function () {
        // Dashboard stats
        Route::get('/statistik', [ApiController::class, 'statistik'])->name('statistik');
        Route::get('/kalender', [ApiController::class, 'kalender'])->name('kalender');
        Route::get('/notifikasi', [ApiController::class, 'notifikasi'])->name('notifikasi');

        // Team workload
        Route::get('/workload-tim', [ApiController::class, 'workloadTim'])->name('workload-tim');
        Route::get('/progress-anggota/{pegawai}', [ApiController::class, 'progressAnggota'])->name('progress-anggota');

        // Tugas Pokok by Pegawai
        Route::get('/pegawai/{pegawai}/tugas-pokok', [ApiController::class, 'tugasPokokByPegawai'])->name('tugas-pokok-by-pegawai');
    });    // ============================================
    // SHARED ROUTES (Used by multiple views)
    // ============================================
    Route::post('/upload-bukti', [PenugasanController::class, 'uploadBukti'])->name('upload-bukti');
    Route::post('/berikan-tugas', [PenugasanController::class, 'berikanTugas'])->name('berikan-tugas');
    Route::post('/buat-tugas', [PenugasanController::class, 'buatTugas'])->name('buat-tugas');
    Route::post('/validasi-tugas/{tugas}', [PenugasanController::class, 'validasiTugas'])->name('validasi-tugas');
    Route::post('/preview-penilaian', [PenugasanController::class, 'previewPenilaian'])->name('preview-penilaian');

    // ============================================
    // LEGACY ROUTES (Backward Compatibility)
    // TODO: Remove after migration complete
    // ============================================
    Route::get('/daftar-pegawai', [PenugasanController::class, 'index'])->name('index');
    Route::get('/pegawai/{id}', [PenugasanController::class, 'show'])->name('show');
});

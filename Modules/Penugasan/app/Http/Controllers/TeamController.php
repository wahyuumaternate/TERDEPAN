<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Services\HitungKeterlambatan;

/**
 * TeamController
 *
 * Menu "Manajemen Penugasan" (Tim Saya, Monitoring Tim, Detail Anggota) sudah
 * dihapus dari sidebar & routing — lihat catatan pembersihan di respons chat
 * saat penghapusan dilakukan. Controller ini kini hanya menyisakan endpoint
 * yang masih dipakai halaman detail tugas (modal "Beri Penilaian").
 */
class TeamController extends Controller
{
    /**
     * Preview nilai_akhir (bobot × realisasi / 100, dipotong persentase keterlambatan)
     * sebelum disimpan — dipakai modal "Beri Penilaian" di halaman detail tugas.
     * Dihitung lewat HitungKeterlambatan yang sama dengan PenugasanActionService,
     * bukan direplikasi di JavaScript (dok. 08 §6, Modal Beri Penilaian).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function previewPenilaian(Request $request, HitungKeterlambatan $hitungKeterlambatan)
    {
        $validated = $request->validate([
            'penugasan_id' => 'required|exists:knj_penugasan,id',
            'bobot_persen' => 'required|numeric|min:0|max:100',
            'realisasi_persen' => 'required|numeric|min:0|max:100',
        ]);

        $penugasan = Penugasan::findOrFail($validated['penugasan_id']);

        $deadline = $penugasan->deadline_terbaru ?? $penugasan->tanggal_selesai;
        $tanggalDiselesaikan = $penugasan->tanggal_diselesaikan ?? now();
        $persentaseTerlambat = $hitungKeterlambatan->persentase($deadline, $tanggalDiselesaikan);

        $nilaiAwal = round(($validated['bobot_persen'] * $validated['realisasi_persen']) / 100, 2);
        $nilaiAkhir = $penugasan->hitungNilaiAkhir($nilaiAwal, $persentaseTerlambat);

        return response()->json([
            'success' => true,
            'data' => [
                'nilai_awal' => $nilaiAwal,
                'persentase_terlambat' => $persentaseTerlambat,
                'nilai_akhir' => $nilaiAkhir,
            ],
        ]);
    }
}

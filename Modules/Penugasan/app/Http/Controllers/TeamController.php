<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Services\HitungKeterlambatan;

/**
 * TeamController
 *
 * Mengelola tim dan tugas untuk atasan/supervisor
 * Fitur: overview tim, monitoring. Berikan tugas & validasi kini bagian dari
 * halaman gabungan penugasan.tugas-saya (tab "diberikan") — lihat dok. 08 §4.1.
 */
class TeamController extends Controller
{
    use AuthorizesRequests;

    private const STATUS_AKTIF = [Penugasan::STATUS_PROSES, Penugasan::STATUS_REVISI, Penugasan::STATUS_TERLAMBAT];

    /**
     * Menampilkan daftar anggota tim
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get anggota tim (bawahan langsung)
        $anggotaTim = User::whereRelation('profile', 'atasan_langsung_id', $user->id)
            ->whereRelation('profile', 'status_aktif', 'Aktif')
            ->with(['profile.jabatan', 'profile.bidang'])
            ->withCount([
                'penugasan as tugas_aktif' => function ($q) {
                    $q->whereIn('status', self::STATUS_AKTIF);
                },
                'penugasan as tugas_selesai' => function ($q) {
                    $q->where('status', Penugasan::STATUS_SELESAI);
                },
                'penugasan as tugas_pending' => function ($q) {
                    $q->where('status', Penugasan::STATUS_PENDING);
                },
            ])
            ->get()
            ->map(function ($anggota) {
                // Hitung workload sederhana
                $workload = ($anggota->tugas_aktif + $anggota->tugas_pending) * 10;
                $anggota->workload_persen = min($workload, 100);

                return $anggota;
            });

        return view('penugasan::tim.index', compact('anggotaTim'));
    }

    /**
     * Menampilkan overview tim dengan statistik lengkap
     *
     * @return \Illuminate\View\View
     */
    public function overview(Request $request)
    {
        $user = $request->user();

        // Get anggota tim dengan detail tugas
        $anggotaTim = User::whereRelation('profile', 'atasan_langsung_id', $user->id)
            ->whereRelation('profile', 'status_aktif', 'Aktif')
            ->with(['profile.jabatan', 'profile.bidang'])
            ->withCount([
                'penugasan as tugas_selesai' => function ($q) {
                    $q->where('status', Penugasan::STATUS_SELESAI);
                },
                'penugasan as tugas_aktif' => function ($q) {
                    $q->whereIn('status', self::STATUS_AKTIF);
                },
                'penugasan as tugas_pending' => function ($q) {
                    $q->where('status', Penugasan::STATUS_PENDING);
                },
                'penugasan as tugas_tambahan_aktif' => function ($q) {
                    $q->where('jenis', 'tambahan')->whereIn('status', self::STATUS_AKTIF);
                },
            ])
            ->get()
            ->map(function ($anggota) {
                $workload = ($anggota->tugas_aktif + $anggota->tugas_pending) * 10;
                $anggota->workload_persen = min($workload, 100);

                return $anggota;
            });

        // Statistik tim
        $statistikTim = [
            'total_anggota' => $anggotaTim->count(),
            'rata_rata_workload' => $anggotaTim->avg('workload_persen'),
            'total_tugas_aktif' => $anggotaTim->sum('tugas_aktif'),
            'perlu_validasi' => Penugasan::whereIn('pegawai_id', $anggotaTim->pluck('id'))
                ->where('status', Penugasan::STATUS_SELESAI)
                ->whereNull('realisasi_persen')
                ->count(),
            'tugas_terlambat' => Penugasan::whereIn('pegawai_id', $anggotaTim->pluck('id'))
                ->where('status', Penugasan::STATUS_TERLAMBAT)
                ->count(),
        ];

        return view('penugasan::tim.overview', compact('anggotaTim', 'statistikTim'));
    }

    /**
     * Menampilkan detail anggota tim dengan daftar penugasan (pokok & tambahan)
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function detailAnggota(Request $request, $id)
    {
        $user = $request->user();

        // Cek apakah pegawai adalah bawahan
        $pegawai = User::where('id', $id)
            ->whereRelation('profile', 'atasan_langsung_id', $user->id)
            ->with(['profile.jabatan', 'profile.bidang'])
            ->firstOrFail();

        $tahun = $request->get('tahun', date('Y'));

        $tahuns = Penugasan::where('pegawai_id', $id)
            ->selectRaw('EXTRACT(YEAR FROM tanggal_mulai) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->filter();

        $query = Penugasan::where('pegawai_id', $id)
            ->with(['attachedFiles', 'pemberiTugas:id,nama', 'validator:id,nama'])
            ->whereYear('tanggal_mulai', $tahun);

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sortBy = $request->get('sort_by', 'tanggal_mulai');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $penugasanList = $query->paginate($request->get('per_page', 10));

        $statsQuery = Penugasan::where('pegawai_id', $id)->whereYear('tanggal_mulai', $tahun);
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', Penugasan::STATUS_PENDING)->count(),
            'dikerjakan' => (clone $statsQuery)->whereIn('status', self::STATUS_AKTIF)->count(),
            'selesai' => (clone $statsQuery)->where('status', Penugasan::STATUS_SELESAI)->count(),
            'pokok' => (clone $statsQuery)->where('jenis', 'pokok')->count(),
            'tambahan' => (clone $statsQuery)->where('jenis', 'tambahan')->count(),
        ];

        return view('penugasan::tim.detail-anggota', compact('pegawai', 'penugasanList', 'tahuns', 'tahun', 'stats'));
    }

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

    /**
     * Menampilkan dashboard monitoring tim
     *
     * @return \Illuminate\View\View
     */
    public function monitoring(Request $request)
    {
        $user = $request->user();

        $anggotaTim = User::whereRelation('profile', 'atasan_langsung_id', $user->id)
            ->whereRelation('profile', 'status_aktif', 'Aktif')
            ->with(['profile.jabatan', 'profile.bidang'])
            ->withCount([
                'penugasan as tugas_aktif' => function ($q) {
                    $q->whereIn('status', self::STATUS_AKTIF);
                },
                'penugasan as tugas_selesai' => function ($q) {
                    $q->where('status', Penugasan::STATUS_SELESAI);
                },
                'penugasan as tugas_pending' => function ($q) {
                    $q->where('status', Penugasan::STATUS_PENDING);
                },
                'penugasan as tugas_terlambat' => function ($q) {
                    $q->where('status', Penugasan::STATUS_TERLAMBAT);
                },
            ])
            ->get();

        return view('penugasan::tim.monitoring', compact('anggotaTim'));
    }

    /**
     * Menambahkan catatan monitoring
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function catatanMonitoring(Request $request)
    {
        // TODO: Implement catatan monitoring
        return response()->json([
            'success' => true,
            'message' => 'Catatan monitoring berhasil disimpan',
        ]);
    }
}

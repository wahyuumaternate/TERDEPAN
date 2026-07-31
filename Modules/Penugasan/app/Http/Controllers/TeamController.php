<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\Penugasan\Models\TugasHarian;
use Modules\Penugasan\Models\TugasPokok;
use Modules\Penugasan\Models\TugasTambahan;

/**
 * TeamController
 *
 * Mengelola tim dan tugas untuk atasan/supervisor
 * Fitur: overview tim, berikan tugas, validasi, monitoring
 */
class TeamController extends Controller
{
    use AuthorizesRequests;

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
                'tugasHarian as tugas_aktif' => function ($q) {
                    $q->whereIn('status', ['dikerjakan', 'validasi']);
                },
                'tugasHarian as tugas_selesai' => function ($q) {
                    $q->where('status', 'selesai');
                },
                'tugasHarian as tugas_pending' => function ($q) {
                    $q->where('status', 'pending');
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
                'tugasHarian as tugas_selesai' => function ($q) {
                    $q->where('status', 'selesai');
                },
                'tugasHarian as tugas_aktif' => function ($q) {
                    $q->whereIn('status', ['dikerjakan', 'validasi']);
                },
                'tugasHarian as tugas_pending' => function ($q) {
                    $q->where('status', 'pending');
                },
                'tugasTambahan as tugas_tambahan_aktif' => function ($q) {
                    $q->whereIn('status', ['dikerjakan', 'validasi']);
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
            'perlu_validasi' => TugasHarian::whereIn('pegawai_id', $anggotaTim->pluck('id'))
                ->where('status', 'validasi')
                ->count(),
            'tugas_terlambat' => TugasHarian::whereIn('pegawai_id', $anggotaTim->pluck('id'))
                ->whereIn('status', ['dikerjakan', 'validasi'])
                ->where('tanggal_selesai', '<', now())
                ->count(),
        ];

        return view('penugasan::tim.overview', compact('anggotaTim', 'statistikTim'));
    }

    /**
     * Menampilkan detail anggota tim dengan daftar tugas pokok, harian, dan tambahan
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

        // Query tugas pokok untuk pegawai ini
        $query = TugasPokok::where('pegawai_id', $id)
            ->with([
                'perjanjianKinerja',
                'indikatorPK',
                'attachedFiles',
                'progress',
            ])
            ->whereYear('tanggal_mulai', $tahun);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_tugas', 'like', "%{$request->search}%")
                    ->orWhere('deskripsi', 'like', "%{$request->search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'tanggal_mulai');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $tugasPokok = $query->paginate($request->get('per_page', 10));

        // Get tahun options untuk pegawai ini
        $tahuns = TugasPokok::where('pegawai_id', $id)
            ->selectRaw('EXTRACT(YEAR FROM tanggal_mulai) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->filter();

        // Query tugas harian untuk pegawai ini
        $tugasHarianQuery = TugasHarian::where('pegawai_id', $id)
            ->with([
                'tugasPokok',
                'pemberiTugas',
                'attachedFiles',
                'progress',
            ])
            ->whereYear('tanggal_mulai', $tahun);

        // Sort tugas harian
        $tugasHarianQuery->orderBy('tanggal_mulai', 'desc');

        $tugasHarian = $tugasHarianQuery->paginate($request->get('per_page_harian', 10), ['*'], 'page_harian');

        // Get tugas tambahan list for this pegawai
        $tugasTambahanList = TugasTambahan::with([
            'pemberiTugas',
            'validator',
            'attachedFiles',
        ])->where('pegawai_id', $pegawai->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Statistics untuk pegawai ini
        // Total Tugas Pokok
        $totalTugasPokok = TugasPokok::where('pegawai_id', $id)
            ->whereYear('tanggal_mulai', $tahun)
            ->count();

        // Total Tugas Harian
        $totalTugasHarian = TugasHarian::where('pegawai_id', $id)
            ->whereYear('tanggal_mulai', $tahun)
            ->count();

        // Tugas Harian Selesai
        $tugasSelesai = TugasHarian::where('pegawai_id', $id)
            ->whereYear('tanggal_mulai', $tahun)
            ->where('status', 'selesai')
            ->count();

        // Tugas Harian Berjalan (dikerjakan + revisi)
        $tugasBerjalan = TugasHarian::where('pegawai_id', $id)
            ->whereYear('tanggal_mulai', $tahun)
            ->whereIn('status', ['dikerjakan', 'revisi'])
            ->count();

        // Tugas Tambahan Stats
        $totalTugasTambahan = $tugasTambahanList->count();
        $tugasTambahanSelesai = $tugasTambahanList->where('status', 'selesai')->count();
        $tugasTambahanProgress = $tugasTambahanList->whereIn('status', ['dikerjakan', 'revisi', 'validasi'])->count();

        // Get all tugas pokok for dropdown (not paginated)
        $tugasPokokList = TugasPokok::where('pegawai_id', $id)
            ->orderBy('nama_tugas')
            ->get();

        // Flag untuk menunjukkan bahwa ini diakses dari "Tim Saya"
        $fromTimSaya = true;

        return view('penugasan::penugasan.detail', compact(
            'pegawai',
            'tugasPokok',
            'tugasHarian',
            'tahuns',
            'tahun',
            'tugasTambahanList',
            'totalTugasPokok',
            'totalTugasHarian',
            'tugasSelesai',
            'tugasBerjalan',
            'totalTugasTambahan',
            'tugasTambahanSelesai',
            'tugasTambahanProgress',
            'tugasPokokList',
            'fromTimSaya'
        ));
    }

    /**
     * Menampilkan form untuk memberikan tugas
     *
     * @return \Illuminate\View\View
     */
    public function formBerikanTugas(Request $request)
    {
        $user = $request->user();
        $kodeJabatan = $user->profile?->jabatan?->kode;

        // Get daftar pegawai untuk tugas harian berdasarkan role
        if (in_array($kodeJabatan, ['KABAN', 'SEKBAN'])) {
            // KABAN dan SEKBAN bisa memberi tugas harian ke semua pegawai
            $bawahanLangsung = User::whereRelation('profile', 'status_aktif', 'Aktif')
                ->where('id', '!=', $user->id)
                ->whereHas('profile.jabatan', function ($q) {
                    $q->where('kode', '!=', 'GATEK'); // Exclude GATEK
                })
                ->with(['profile.jabatan', 'profile.bidang'])
                ->orderBy('nama')
                ->get();
        } elseif ($kodeJabatan === 'KABID') {
            // KABID bisa memberi tugas harian ke pegawai di bidangnya
            $bawahanLangsung = User::whereRelation('profile', 'status_aktif', 'Aktif')
                ->where('id', '!=', $user->id)
                ->whereRelation('profile', 'bidang_id', $user->profile?->bidang_id)
                ->with(['profile.jabatan', 'profile.bidang'])
                ->orderBy('nama')
                ->get();
        } else {
            // KASUBAG dan lainnya hanya bawahan langsung
            $bawahanLangsung = User::whereRelation('profile', 'atasan_langsung_id', $user->id)
                ->whereRelation('profile', 'status_aktif', 'Aktif')
                ->with(['profile.jabatan', 'profile.bidang'])
                ->orderBy('nama')
                ->get();
        }

        // Get daftar pegawai untuk tugas tambahan berdasarkan role
        $pegawaiTugasTambahan = collect();

        if (in_array($kodeJabatan, ['KABAN', 'SEKBAN'])) {
            // KABAN dan SEKBAN bisa memberi tugas tambahan ke siapa saja
            $pegawaiTugasTambahan = User::whereRelation('profile', 'status_aktif', 'Aktif')
                ->where('id', '!=', $user->id)
                ->whereHas('profile.jabatan', function ($q) {
                    $q->where('kode', '!=', 'GATEK'); // Exclude GATEK
                })
                ->with(['profile.jabatan', 'profile.bidang'])
                ->orderBy('nama')
                ->get();
        } elseif ($kodeJabatan === 'KABID') {
            // KABID bisa memberi tugas tambahan ke pegawai di bidangnya + semua GATEK
            $pegawaiTugasTambahan = User::whereRelation('profile', 'status_aktif', 'Aktif')
                ->where('id', '!=', $user->id)
                ->where(function ($q) use ($user) {
                    // Pegawai di bidang yang sama
                    $q->whereRelation('profile', 'bidang_id', $user->profile?->bidang_id)
                        // ATAU semua GATEK
                        ->orWhereHas('jabatan', function ($subQ) {
                            $subQ->where('kode', 'GATEK');
                        });
                })
                ->with(['profile.jabatan', 'profile.bidang'])
                ->orderBy('nama')
                ->get();
        } elseif ($kodeJabatan === 'KASUBAG') {
            // KASUBAG bisa memberi tugas tambahan ke bawahan langsung + semua GATEK
            $pegawaiTugasTambahan = User::whereRelation('profile', 'status_aktif', 'Aktif')
                ->where('id', '!=', $user->id)
                ->where(function ($q) use ($user) {
                    // Bawahan langsung
                    $q->whereRelation('profile', 'atasan_langsung_id', $user->id)
                        // ATAU semua GATEK
                        ->orWhereHas('profile.jabatan', function ($subQ) {
                            $subQ->where('kode', 'GATEK');
                        });
                })
                ->with(['profile.jabatan', 'profile.bidang'])
                ->orderBy('nama')
                ->get();
        } else {
            // Lainnya hanya bawahan langsung
            $pegawaiTugasTambahan = $bawahanLangsung;
        }

        // Get recent assignments (7 hari terakhir)
        $recentAssignments = collect();

        $tugasHarianRecent = TugasHarian::whereIn('pegawai_id', $bawahanLangsung->pluck('id'))
            ->where('created_at', '>=', now()->subDays(7))
            ->with('pegawai')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $tugasTambahanRecent = TugasTambahan::whereIn('pegawai_id', $bawahanLangsung->pluck('id'))
            ->where('created_at', '>=', now()->subDays(7))
            ->with('pegawai')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentAssignments = $tugasHarianRecent->merge($tugasTambahanRecent)->sortByDesc('created_at')->take(5);

        // Stats
        $stats = [
            'tugas_aktif' => TugasHarian::whereIn('pegawai_id', $bawahanLangsung->pluck('id'))
                ->whereIn('status', ['dikerjakan', 'validasi'])
                ->count(),
            'menunggu_validasi' => TugasHarian::whereIn('pegawai_id', $bawahanLangsung->pluck('id'))
                ->where('status', 'validasi')
                ->count(),
            'avg_progress' => 0,
        ];

        return view('penugasan::tim.berikan-tugas', compact('bawahanLangsung', 'pegawaiTugasTambahan', 'recentAssignments', 'stats'));
    }

    /**
     * Proses memberikan tugas ke anggota tim
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function berikanTugas(Request $request)
    {
        // Delegate to PenugasanController for now
        $controller = new \Modules\Penugasan\Http\Controllers\PenugasanController;

        return $controller->berikanTugas($request);
    }

    /**
     * Menampilkan daftar tugas yang perlu divalidasi
     *
     * @return \Illuminate\View\View
     */
    public function daftarValidasi(Request $request)
    {
        $user = $request->user();

        // Get tugas yang perlu validasi dari bawahan
        $tugasValidasi = TugasHarian::whereHas('pegawai', function ($q) use ($user) {
            $q->whereRelation('profile', 'atasan_langsung_id', $user->id);
        })
            ->where('status', 'validasi')
            ->with([
                'tugasPokok:id,nama_tugas',
                'pegawai:id,nama',
                'pegawai.profile:id,user_id,jabatan_id,bidang_id',
                'pegawai.profile.jabatan:id,nama',
                'pegawai.profile.bidang:id,nama',
                'attachedFiles',
            ])
            ->orderBy('tanggal_selesai', 'asc')
            ->paginate(20);

        return view('penugasan::tim.daftar-validasi', compact('tugasValidasi'));
    }

    /**
     * Proses validasi tugas
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function validasiTugas(Request $request, $id)
    {
        // Delegate to PenugasanController for now
        $controller = new \Modules\Penugasan\Http\Controllers\PenugasanController;

        return $controller->validasiTugas($request, $id);
    }

    /**
     * Preview penilaian sebelum validasi
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function previewPenilaian(Request $request)
    {
        // Delegate to PenugasanController for now
        $controller = new \Modules\Penugasan\Http\Controllers\PenugasanController;

        return $controller->previewPenilaian($request);
    }

    /**
     * Menampilkan dashboard monitoring tim
     *
     * @return \Illuminate\View\View
     */
    public function monitoring(Request $request)
    {
        $user = $request->user();

        // Get anggota tim dengan statistik
        $anggotaTim = User::whereRelation('profile', 'atasan_langsung_id', $user->id)
            ->whereRelation('profile', 'status_aktif', 'Aktif')
            ->with(['profile.jabatan', 'profile.bidang'])
            ->withCount([
                'tugasHarian as tugas_aktif' => function ($q) {
                    $q->whereIn('status', ['dikerjakan', 'validasi']);
                },
                'tugasHarian as tugas_selesai' => function ($q) {
                    $q->where('status', 'selesai');
                },
                'tugasHarian as tugas_pending' => function ($q) {
                    $q->where('status', 'pending');
                },
                'tugasHarian as tugas_terlambat' => function ($q) {
                    $q->whereIn('status', ['dikerjakan', 'validasi'])->where('tanggal_selesai', '<', now());
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

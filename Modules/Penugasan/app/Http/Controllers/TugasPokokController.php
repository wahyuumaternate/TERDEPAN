<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterBidang;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Penugasan\Models\TugasPokok;

/**
 * TugasPokokController
 *
 * Mengelola tugas pokok yang bersumber dari Perjanjian Kinerja (PK)
 * Tugas pokok merupakan breakdown dari indikator PK (60-70% dari total pekerjaan)
 */
class TugasPokokController extends Controller
{
    use AuthorizesRequests;

    /**
     * Menampilkan daftar tugas pokok (untuk atasan/admin)
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $kodeJabatan = $user->profile?->jabatan?->kode;

        // Cek akses: Admin, Kaban, Sekban, Kabid
        if (! in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID'])) {
            abort(403, 'Tidak memiliki akses ke halaman ini');
        }

        $query = TugasPokok::with([
            'pegawai:id,nama',
            'pegawai.profile:id,user_id,jabatan_id,bidang_id',
            'pegawai.profile.jabatan:id,nama',
            'pegawai.profile.bidang:id,nama',
            'perjanjianKinerja:id,periode_mulai,periode_selesai',
            'indikatorPK:id,indikator_sasaran',
        ]);

        // Filter berdasarkan role
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            // Lihat semua
        } elseif ($kodeJabatan === 'KABID') {
            // Lihat hanya bidangnya
            $query->whereHas('pegawai', function ($q) use ($user) {
                $q->whereRelation('profile', 'bidang_id', $user->profile?->bidang_id);
            });
        }

        // Filter berdasarkan tahun
        $tahun = $request->get('tahun', date('Y'));
        $query->whereYear('tanggal_mulai', $tahun);

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan bidang
        if ($request->filled('bidang_id')) {
            $query->whereHas('pegawai', function ($q) use ($request) {
                $q->whereRelation('profile', 'bidang_id', $request->bidang_id);
            });
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_tugas', 'like', "%{$request->search}%")
                    ->orWhere('deskripsi', 'like', "%{$request->search}%")
                    ->orWhereHas('pegawai', function ($subQ) use ($request) {
                        $subQ->where('nama', 'like', "%{$request->search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'tanggal_mulai');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $tugasPokok = $query->paginate($request->get('per_page', 20));

        // Get filter options
        $tahuns = TugasPokok::selectRaw('EXTRACT(YEAR FROM tanggal_mulai) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        $bidangs = MasterBidang::where('is_active', true)
            ->select('id', 'nama')
            ->get();

        // Get pegawai list for filter
        $pegawaiQuery = User::whereRelation('profile', 'status_aktif', 'Aktif');

        if ($kodeJabatan === 'KABID') {
            $pegawaiQuery->whereRelation('profile', 'bidang_id', $user->profile?->bidang_id);
        }

        $pegawaiList = $pegawaiQuery->select('id', 'nama')->orderBy('nama')->get();

        // Stats
        $statsQuery = TugasPokok::query();

        if ($kodeJabatan === 'KABID') {
            $statsQuery->whereHas('pegawai', function ($q) use ($user) {
                $q->whereRelation('profile', 'bidang_id', $user->profile?->bidang_id);
            });
        }

        $stats = [
            'total' => $statsQuery->count(),
            'pending' => $statsQuery->where('status', 'pending')->count(),
            'dikerjakan' => $statsQuery->where('status', 'dikerjakan')->count(),
            'selesai' => $statsQuery->where('status', 'selesai')->count(),
        ];

        return view('penugasan::tugas-pokok.index', compact(
            'tugasPokok',
            'tahuns',
            'bidangs',
            'tahun',
            'pegawaiList',
            'stats'
        ));
    }

    /**
     * Menampilkan daftar tugas pokok milik user yang sedang login
     *
     * @return \Illuminate\View\View
     */
    public function tugasSaya(Request $request)
    {
        $user = $request->user();

        $query = TugasPokok::where('pegawai_id', $user->id)
            ->with([
                'perjanjianKinerja:id,periode_mulai,periode_selesai',
                'indikatorPK:id,indikator_sasaran,target_value,satuan',
                'tugasHarian' => function ($q) {
                    $q->select('id', 'tugas_pokok_id', 'nama_tugas', 'deskripsi', 'status', 'is_mandiri', 'tanggal_selesai')
                        ->orderByRaw("CASE 
                          WHEN status = 'revisi' THEN 1
                          WHEN status = 'dikerjakan' THEN 2
                          WHEN status = 'pending' THEN 3
                          WHEN status = 'validasi' THEN 4
                          WHEN status = 'selesai' THEN 5
                          ELSE 99 END");
                },
            ])
            ->withCount([
                'tugasHarian as total_tugas_harian',
                'tugasHarian as selesai_count' => function ($q) {
                    $q->where('status', 'selesai');
                },
            ]);

        // Filter berdasarkan status
        $status = $request->get('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Filter berdasarkan tahun
        $tahun = $request->get('tahun', date('Y'));
        $query->whereYear('tanggal_mulai', $tahun);

        // Sort
        $sortBy = $request->get('sort_by', 'tanggal_mulai');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $tugasPokok = $query->paginate(20);

        // Get tahun options
        $tahuns = TugasPokok::where('pegawai_id', $user->id)
            ->selectRaw('EXTRACT(YEAR FROM tanggal_mulai) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (empty($tahuns)) {
            $tahuns = [date('Y')];
        }

        return view('penugasan::tugas-pokok.tugas-saya', compact(
            'tugasPokok',
            'tahuns',
            'tahun',
            'status'
        ));
    }

    /**
     * Menampilkan detail tugas pokok
     *
     * @param  string  $id
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $id)
    {
        $tugasPokok = TugasPokok::with([
            'pegawai:id,nama',
            'pegawai.profile:id,user_id,jabatan_id,bidang_id',
            'pegawai.profile.jabatan:id,nama',
            'pegawai.profile.bidang:id,nama',
            'perjanjianKinerja:id,periode_mulai,periode_selesai,tahun',
            'indikatorPK:id,indikator_sasaran,target_value,satuan',
            'tugasHarian' => function ($q) {
                $q->select(
                    'id',
                    'tugas_pokok_id',
                    'nama_tugas',
                    'tanggal_mulai',
                    'tanggal_selesai',
                    'status',
                    'progress_persen',
                    'nilai_akhir'
                )->orderBy('tanggal_mulai', 'desc');
            },
            'progress' => function ($q) {
                $q->orderBy('tanggal', 'desc')->limit(10);
            },
            'attachedFiles',
        ])->findOrFail($id);

        // Authorization check
        $this->authorize('view', $tugasPokok);

        // Statistik tugas harian
        $statistikTugasHarian = [
            'total' => $tugasPokok->tugasHarian->count(),
            'selesai' => $tugasPokok->tugasHarian->where('status', 'selesai')->count(),
            'dikerjakan' => $tugasPokok->tugasHarian->where('status', 'dikerjakan')->count(),
            'pending' => $tugasPokok->tugasHarian->where('status', 'pending')->count(),
            'rata_rata_nilai' => $tugasPokok->tugasHarian
                ->whereNotNull('nilai_akhir')
                ->avg('nilai_akhir'),
        ];

        return view('penugasan::tugas-pokok.show', compact(
            'tugasPokok',
            'statistikTugasHarian'
        ));
    }

    /**
     * Membuat tugas pokok secara mandiri (tanpa sumber Perjanjian Kinerja).
     *
     * Selama modul PerjanjianKinerja dinonaktifkan, ini menjadi satu-satunya
     * jalur pembuatan Tugas Pokok — atasan mengisi langsung nama tugas,
     * deskripsi, bobot, dan target tanpa perlu indikator PK.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:users,id',
            'nama_tugas' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'bobot_persen' => 'required|numeric|min:0|max:100',
            'target_value' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:30',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tugasPokok = TugasPokok::create([
            'pegawai_id' => $validated['pegawai_id'],
            'perjanjian_kinerja_id' => null,
            'indikator_id' => null,
            'nama_tugas' => $validated['nama_tugas'],
            'deskripsi' => $validated['deskripsi'],
            'bobot_persen' => $validated['bobot_persen'],
            'target_value' => $validated['target_value'],
            'satuan' => $validated['satuan'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas pokok berhasil dibuat',
            'data' => $tugasPokok,
        ], 201);
    }

    /**
     * Sinkronisasi data tugas pokok dari Perjanjian Kinerja
     * Hanya mensinkronkan untuk pegawai yang sedang login
     *
     * Catatan: dinonaktifkan dari UI selama modul PerjanjianKinerja non-aktif
     * (lihat Modules/Penugasan/routes/web.php), tapi method ini tetap
     * dipertahankan agar bisa diaktifkan kembali bila PK diaktifkan lagi.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sinkronData(Request $request)
    {
        $userId = $request->user()->id;
        $created = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            // Query dengan join yang benar: pk_indikator -> pk_sasaran -> pk_perjanjian_kinerja
            // Filter hanya untuk pegawai yang sedang login
            $rows = DB::table('pk_indikator as i')
                ->join('pk_sasaran as s', 'i.sasaran_id', '=', 's.id')
                ->join('pk_perjanjian_kinerja as p', 's.perjanjian_kinerja_id', '=', 'p.id')
                ->join('user_profiles as peg_profile', 'p.pegawai_id', '=', 'peg_profile.user_id')
                ->where('p.pegawai_id', $userId) // Hanya untuk user yang login
                ->where('peg_profile.status_aktif', 'Aktif')
                ->where('p.status_dokumen', '!=', 'Draft') // Hanya sinkron yang sudah bukan draft
                ->whereNull('p.deleted_at') // Tidak yang sudah dihapus
                ->select(
                    'p.id as perjanjian_kinerja_id',
                    'i.id as indikator_id',
                    'p.pegawai_id',
                    DB::raw("COALESCE(NULLIF(i.indikator_sasaran,''), concat('Indikator ', i.id)) as indikator_nama"),
                    DB::raw("COALESCE(i.keterangan, '') as indikator_deskripsi"),
                    'p.periode_mulai as perjanjian_periode_mulai',
                    'p.periode_selesai as perjanjian_periode_selesai',
                    DB::raw('COALESCE(i.target_value, 0) as indikator_target'),
                    DB::raw("COALESCE(i.satuan, '-') as indikator_satuan")
                )
                ->get();

            foreach ($rows as $r) {
                $exists = TugasPokok::where('perjanjian_kinerja_id', $r->perjanjian_kinerja_id)
                    ->where('indikator_id', $r->indikator_id)
                    ->exists();

                if ($exists) {
                    $skipped++;

                    continue;
                }

                $periode_mulai = $r->perjanjian_periode_mulai ?? \Carbon\Carbon::now()->startOfYear()->toDateString();
                $periode_selesai = $r->perjanjian_periode_selesai ?? \Carbon\Carbon::now()->endOfYear()->toDateString();

                TugasPokok::create([
                    'perjanjian_kinerja_id' => $r->perjanjian_kinerja_id,
                    'pegawai_id' => $r->pegawai_id,
                    'indikator_id' => $r->indikator_id,
                    'nama_tugas' => $r->indikator_nama ?? 'Tugas indikator '.$r->indikator_id,
                    'deskripsi' => $r->indikator_deskripsi ?? '',
                    'bobot_persen' => 60, // Default bobot, bisa disesuaikan
                    'tanggal_mulai' => $periode_mulai,
                    'tanggal_selesai' => $periode_selesai,
                    'target_value' => $r->indikator_target ?? 0,
                    'satuan' => $r->indikator_satuan ?? '-',
                ]);

                $created++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sinkronisasi berhasil',
                'created' => $created,
                'skipped' => $skipped,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Sinkronisasi gagal: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update status tugas pokok
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,dikerjakan,selesai',
        ]);

        $tugasPokok = TugasPokok::findOrFail($id);

        // Authorization
        $this->authorize('update', $tugasPokok);

        $tugasPokok->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status tugas berhasil diperbarui',
        ]);
    }
}

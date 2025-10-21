<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\MasterPegawai;
use Illuminate\Http\Request;
use Modules\Penugasan\Models\TugasPokok;
use Illuminate\Support\Facades\DB;

class TugasPokokController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Default filter tahun sekarang berdasarkan periode_mulai
        $tahun = $request->get('tahun', date('Y'));

        // Query untuk mendapatkan daftar pegawai dengan statistik tugas pokok
        $pegawaiQuery = MasterPegawai::where('status_aktif', 'Aktif')
            ->with(['jabatan', 'bidang'])
            ->withCount([
                'tugasPokok as total_tugas' => function ($q) use ($tahun) {
                    $q->whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun]);
                },
                'tugasPokok as pending_tugas' => function ($q) use ($tahun) {
                    $q->whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])
                        ->where('status', 'Pending');
                },
                'tugasPokok as diterima_tugas' => function ($q) use ($tahun) {
                    $q->whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])
                        ->where('status', 'Diterima');
                },
                'tugasPokok as dikerjakan_tugas' => function ($q) use ($tahun) {
                    $q->whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])
                        ->where('status', 'Dikerjakan');
                },
                'tugasPokok as selesai_tugas' => function ($q) use ($tahun) {
                    $q->whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])
                        ->where('status', 'Selesai');
                }
            ]);

        // Filter by jabatan
        if ($request->filled('jabatan_id')) {
            $pegawaiQuery->where('jabatan_id', $request->jabatan_id);
        }

        // Filter by bidang
        if ($request->filled('bidang_id')) {
            $pegawaiQuery->where('bidang_id', $request->bidang_id);
        }

        // Search
        if ($request->filled('search')) {
            $pegawaiQuery->where(function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                    ->orWhere('nip', 'like', "%{$request->search}%");
            });
        }

        // Only show pegawai yang punya tugas
        if ($request->get('has_tugas', false)) {
            $pegawaiQuery->has('tugasPokok');
        }

        // Sort - default by jabatan then nomor_identitas
        if ($request->has('sort_by')) {
            $sortBy = $request->get('sort_by');
            $sortOrder = $request->get('sort_order', 'asc');
            $pegawaiQuery->orderBy($sortBy, $sortOrder);
        } else {
            // Default sorting: jabatan_id ASC, then nomor_identitas ASC
            $pegawaiQuery->orderBy('jabatan_id', 'asc')
                ->orderBy('nomor_identitas', 'asc');
        }

        $pegawaiList = $pegawaiQuery->paginate($request->get('per_page', 15));

        // Get filter options
        $tahuns = TugasPokok::selectRaw('EXTRACT(YEAR FROM periode_mulai) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->filter();

        $jabatans = MasterJabatan::where('is_active', true)
            ->orderBy('level')
            ->get();

        $bidangs = MasterBidang::where('is_active', true)
            ->orderBy('nama')
            ->get();

        // Statistics
        $stats = [
            'total_pegawai' => MasterPegawai::where('status_aktif', 'Aktif')->count(),
            'pegawai_dengan_tugas' => MasterPegawai::where('status_aktif', 'Aktif')
                ->whereHas('tugasPokok', function ($q) use ($tahun) {
                    $q->whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun]);
                })->count(),
            'total_tugas' => TugasPokok::whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])->count(),
            'pending' => TugasPokok::whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])
                ->where('status', 'Pending')->count(),
            'dikerjakan' => TugasPokok::whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])
                ->where('status', 'Dikerjakan')->count(),
            'selesai' => TugasPokok::whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])
                ->where('status', 'Selesai')->count(),
        ];

        return view('penugasan::penugasan.tugas-pokok.index', compact(
            'pegawaiList',
            'tahuns',
            'tahun',
            'jabatans',
            'bidangs',
            'stats'
        ));
    }

    /**
     * Show the specified resource.
     */
    public function show(Request $request, $id)
    {
        $pegawai = MasterPegawai::with(['jabatan', 'bidang'])->findOrFail($id);

        $tahun = $request->get('tahun', date('Y'));

        // Query tugas pokok untuk pegawai ini
        $query = TugasPokok::where('pegawai_id', $id)
            ->with([
                'perjanjianKinerja',
                'indikatorPK',
                'dokumenLampiran',
                'progress'
            ])
            ->whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun]);

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
        $sortBy = $request->get('sort_by', 'periode_mulai');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $tugasPokok = $query->paginate($request->get('per_page', 10));

        // Get tahun options untuk pegawai ini
        $tahuns = TugasPokok::where('pegawai_id', $id)
            ->selectRaw('EXTRACT(YEAR FROM periode_mulai) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->filter();

        // Statistics untuk pegawai ini
        $stats = [
            'total' => TugasPokok::where('pegawai_id', $id)
                ->whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])->count(),
            'pending' => TugasPokok::where('pegawai_id', $id)
                ->whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])
                ->where('status', 'Pending')->count(),
            'dikerjakan' => TugasPokok::where('pegawai_id', $id)
                ->whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])
                ->where('status', 'Dikerjakan')->count(),
            'selesai' => TugasPokok::where('pegawai_id', $id)
                ->whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])
                ->where('status', 'Selesai')->count(),
            'total_bobot' => TugasPokok::where('pegawai_id', $id)
                ->whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])
                ->sum('bobot_persen'),
        ];

        return view('penugasan::penugasan.tugas-pokok.show', compact('pegawai', 'tugasPokok', 'tahuns', 'tahun', 'stats'));
    }

    public function sinkronData()
    {
        $created = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            // Query dengan join yang benar: pk_indikator -> pk_sasaran -> pk_perjanjian_kinerja
            $rows = DB::table('pk_indikator as i')
                ->join('pk_sasaran as s', 'i.sasaran_id', '=', 's.id')
                ->join('pk_perjanjian_kinerja as p', 's.perjanjian_kinerja_id', '=', 'p.id')
                ->join('master_pegawai as peg', 'p.pegawai_id', '=', 'peg.id')
                ->where('peg.status_aktif', 'Aktif')
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
                    DB::raw("COALESCE(i.target_value, 0) as indikator_target"),
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
                    'nama_tugas' => $r->indikator_nama ?? 'Tugas indikator ' . $r->indikator_id,
                    'deskripsi' => $r->indikator_deskripsi ?? '',
                    'bobot_persen' => 60, // Default bobot, bisa disesuaikan
                    'periode_mulai' => $periode_mulai,
                    'periode_selesai' => $periode_selesai,
                    'target_value' => $r->indikator_target ?? 0,
                    'satuan' => $r->indikator_satuan ?? '-',
                ]);

                $created++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sinkron selesai',
                'created' => $created,
                'skipped' => $skipped,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Sinkron gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get average progress for the year
     */
    private function getAverageProgress($tahun)
    {
        try {
            $tugasWithProgress = TugasPokok::whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])
                ->whereHas('progress')
                ->with(['progress' => function ($query) {
                    $query->latest();
                }])
                ->get();

            if ($tugasWithProgress->isEmpty()) {
                return 0;
            }

            $totalProgress = $tugasWithProgress->sum(function ($tugas) {
                return $tugas->progress->first()->persentase_progress ?? 0;
            });

            return round($totalProgress / $tugasWithProgress->count(), 2);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Update status tugas pokok
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Pending,Diterima,Dikerjakan,Selesai'
        ]);

        $tugasPokok = TugasPokok::findOrFail($id);
        $tugasPokok->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status tugas berhasil diperbarui'
        ]);
    }
}

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
        $query = TugasPokok::with([
            'pegawai.jabatan',
            'pegawai.bidang',
            'pemberiTugas.jabatan',
            'perjanjianKinerja',
            'indikatorTugas',
            'progress'
        ]);

        // Default filter tahun sekarang berdasarkan periode_mulai
        $tahun = $request->get('tahun', date('Y'));
        $query->whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun]);

        // Filter by pegawai
        if ($request->filled('pegawai_id')) {
            $query->where('pegawai_id', $request->pegawai_id);
        }

        // Filter by jabatan
        if ($request->filled('jabatan_id')) {
            $query->whereHas('pegawai', function ($q) use ($request) {
                $q->where('jabatan_id', $request->jabatan_id);
            });
        }

        // Filter by bidang
        if ($request->filled('bidang_id')) {
            $query->whereHas('pegawai', function ($q) use ($request) {
                $q->where('bidang_id', $request->bidang_id);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by pemberi tugas
        if ($request->filled('pemberi_tugas_id')) {
            $query->where('pemberi_tugas_id', $request->pemberi_tugas_id);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_tugas', 'like', "%{$request->search}%")
                    ->orWhere('deskripsi', 'like', "%{$request->search}%")
                    ->orWhereHas('pegawai', function ($subQ) use ($request) {
                        $subQ->where('nama', 'like', "%{$request->search}%");
                    })
                    ->orWhereHas('pemberiTugas', function ($subQ) use ($request) {
                        $subQ->where('nama', 'like', "%{$request->search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $tugasPokok = $query->paginate($request->get('per_page', 10));

        // Get filter options - Fixed for PostgreSQL
        $tahuns = TugasPokok::selectRaw('EXTRACT(YEAR FROM periode_mulai) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->filter(); // Remove null values if any

        $jabatans = MasterJabatan::where('is_active', true)
            ->orderBy('level')
            ->get();

        $bidangs = MasterBidang::where('is_active', true)
            ->orderBy('nama')
            ->get();

        $pegawais = MasterPegawai::where('status_aktif', 'Aktif')
            ->orderBy('nama')
            ->get();

        $pemberiTugas = MasterPegawai::where('status_aktif', 'Aktif')
            ->whereHas('jabatan', function ($q) {
                $q->where('is_struktural', true);
            })
            ->orderBy('nama')
            ->get();

        // Statistics - Fixed for PostgreSQL
        $stats = [
            'total' => TugasPokok::whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])->count(),
            'pending' => TugasPokok::whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])
                ->where('status', 'Pending')->count(),
            'diterima' => TugasPokok::whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])
                ->where('status', 'Diterima')->count(),
            'dikerjakan' => TugasPokok::whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])
                ->where('status', 'Dikerjakan')->count(),
            'selesai' => TugasPokok::whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])
                ->where('status', 'Selesai')->count(),
            'total_bobot' => TugasPokok::whereRaw('EXTRACT(YEAR FROM periode_mulai) = ?', [$tahun])
                ->sum('bobot_persen'),
            'avg_progress' => $this->getAverageProgress($tahun),
        ];

        return view('penugasan::penugasan.tugas-pokok.index', compact(
            'tugasPokok',
            'tahuns',
            'tahun',
            'jabatans',
            'bidangs',
            'pegawais',
            'pemberiTugas',
            'stats'
        ));
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pegawais = MasterPegawai::where('status_aktif', 'Aktif')
            ->with(['jabatan', 'bidang'])
            ->orderBy('nama')
            ->get();

        $pemberiTugas = MasterPegawai::where('status_aktif', 'Aktif')
            ->whereHas('jabatan', function ($q) {
                $q->where('is_struktural', true);
            })
            ->with('jabatan')
            ->orderBy('nama')
            ->get();

        return view('penugasan::penugasan.tugas-pokok.create', compact('pegawais', 'pemberiTugas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:master_pegawai,id',
            'pemberi_tugas_id' => 'required|exists:master_pegawai,id',
            'nama_tugas' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'periode_mulai' => 'required|date',
            'periode_selesai' => 'required|date|after:periode_mulai',
            'bobot_persen' => 'required|numeric|min:0|max:100',
            'target_output' => 'nullable|string',
            'kualitas_output' => 'nullable|string',
            'waktu_penyelesaian' => 'nullable|string',
            'biaya_aktivitas' => 'nullable|numeric|min:0',
        ]);

        $validated['status'] = 'Pending';

        TugasPokok::create($validated);

        return redirect()->route('tugas-pokok.index')
            ->with('success', 'Tugas pokok berhasil ditambahkan');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $tugasPokok = TugasPokok::with([
            'pegawai.jabatan',
            'pegawai.bidang',
            'pemberiTugas.jabatan',
            'perjanjianKinerja',
            'indikatorTugas',
            'progress'
        ])->findOrFail($id);

        return view('penugasan::penugasan.tugas-pokok.show', compact('tugasPokok'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tugasPokok = TugasPokok::findOrFail($id);

        $pegawais = MasterPegawai::where('status_aktif', 'Aktif')
            ->with(['jabatan', 'bidang'])
            ->orderBy('nama')
            ->get();

        $pemberiTugas = MasterPegawai::where('status_aktif', 'Aktif')
            ->whereHas('jabatan', function ($q) {
                $q->where('is_struktural', true);
            })
            ->with('jabatan')
            ->orderBy('nama')
            ->get();

        return view('penugasan::penugasan.tugas-pokok.edit', compact('tugasPokok', 'pegawais', 'pemberiTugas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $tugasPokok = TugasPokok::findOrFail($id);

        $validated = $request->validate([
            'pegawai_id' => 'required|exists:master_pegawai,id',
            'pemberi_tugas_id' => 'required|exists:master_pegawai,id',
            'nama_tugas' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'periode_mulai' => 'required|date',
            'periode_selesai' => 'required|date|after:periode_mulai',
            'bobot_persen' => 'required|numeric|min:0|max:100',
            'target_output' => 'nullable|string',
            'kualitas_output' => 'nullable|string',
            'waktu_penyelesaian' => 'nullable|string',
            'biaya_aktivitas' => 'nullable|numeric|min:0',
            'status' => 'required|in:Pending,Diterima,Dikerjakan,Selesai',
        ]);

        $tugasPokok->update($validated);

        return redirect()->route('tugas-pokok.index')
            ->with('success', 'Tugas pokok berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tugasPokok = TugasPokok::findOrFail($id);
        $tugasPokok->delete();

        return redirect()->route('tugas-pokok.index')
            ->with('success', 'Tugas pokok berhasil dihapus');
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

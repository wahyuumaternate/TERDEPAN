<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TugasHarianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \Modules\Penugasan\Models\TugasHarian::with(['tugasPokok', 'pegawai', 'pemberiTugas', 'attachedFiles'])
            ->orderBy('tanggal_mulai', 'desc');

        // Filter berdasarkan status
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

        $tugasHarian = $query->paginate($request->get('per_page', 15));

        return view('penugasan::tugas-harian.index', compact('tugasHarian'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('penugasan::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('penugasan::show');
    }

    /**
     * Show the detail page for a specific task
     */
    public function detail($id)
    {
        try {
            $tugasHarian = \Modules\Penugasan\Models\TugasHarian::with([
                'tugasPokok',
                'pegawai.bidang',
                'pegawai.jabatan',
                'pemberiTugas',
                'validator',
                'attachedFiles',
                'historyRevisi.direvisiOleh',
                'historyRevisi.attachedFiles'
            ])->findOrFail($id);

            $pegawai = $tugasHarian->pegawai;
            $tahun = date('Y');

            // Get stats for this pegawai
            $totalTugasPokok = \Modules\Penugasan\Models\TugasPokok::where('pegawai_id', $pegawai->id)->count();
            $totalTugasHarian = \Modules\Penugasan\Models\TugasHarian::where('pegawai_id', $pegawai->id)->count();
            $tugasSelesai = \Modules\Penugasan\Models\TugasHarian::where('pegawai_id', $pegawai->id)
                ->where('status', 'selesai')->count();
            $tugasBerjalan = \Modules\Penugasan\Models\TugasHarian::where('pegawai_id', $pegawai->id)
                ->whereIn('status', ['dikerjakan', 'revisi'])->count();

            // Get tugas harian list for this pegawai
            $tugasHarianList = \Modules\Penugasan\Models\TugasHarian::with([
                'tugasPokok',
                'pemberiTugas',
                'validator',
                'attachedFiles'
            ])->where('pegawai_id', $pegawai->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Get tugas pokok for dropdown in forms
            $tugasPokok = \Modules\Penugasan\Models\TugasPokok::where('pegawai_id', $pegawai->id)->get();

            // Get available years for filter
            $tahuns = \Modules\Penugasan\Models\TugasHarian::selectRaw('EXTRACT(YEAR FROM created_at) as year')
                ->where('pegawai_id', $pegawai->id)
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            if (empty($tahuns)) {
                $tahuns = [date('Y')];
            }

            // Get related data
            $masterPegawai = \App\Models\MasterPegawai::where('status', 'aktif')->get();
            $masterJabatan = \App\Models\MasterJabatan::all();
            $masterBidang = \App\Models\MasterBidang::all();

            return view('penugasan::detail', compact(
                'tugasHarian',
                'pegawai',
                'tahun',
                'totalTugasPokok',
                'totalTugasHarian',
                'tugasSelesai',
                'tugasBerjalan',
                'tugasHarianList',
                'tugasPokok',
                'tahuns',
                'masterPegawai',
                'masterJabatan',
                'masterBidang'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Tugas harian tidak ditemukan');
        }
    }

    /**
     * Show upload eviden page
     */
    public function uploadEviden($id)
    {
        try {
            $tugas = \Modules\Penugasan\Models\TugasHarian::with([
                'tugasPokok',
                'pegawai',
                'attachedFiles'
            ])->findOrFail($id);

            return view('penugasan::penugasan.upload-eviden', compact('tugas'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Tugas tidak ditemukan');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $tugasHarian = \Modules\Penugasan\Models\TugasHarian::with(['tugasPokok', 'pegawai', 'pemberiTugas'])
                ->findOrFail($id);

            // Return JSON response for AJAX request
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json($tugasHarian);
            }

            // Return view for regular request (fallback)
            return view('penugasan::penugasan.tugas-harian.edit', compact('tugasHarian'));
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas harian tidak ditemukan'
                ], 404);
            }
            return redirect()->back()->with('error', 'Tugas harian tidak ditemukan');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nama_tugas' => 'required|string|max:500',
                'deskripsi' => 'nullable|string',
                'tanggal_mulai' => 'required|date',
                'deadline' => 'required|date|after_or_equal:tanggal_mulai',
                'target_value' => 'required|numeric|min:0',
                'satuan' => 'required|string|max:100',
            ]);

            $tugasHarian = \Modules\Penugasan\Models\TugasHarian::findOrFail($id);
            $tugasHarian->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Tugas harian berhasil diperbarui'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $tugasHarian = \Modules\Penugasan\Models\TugasHarian::findOrFail($id);

            // Check if user has permission to delete
            // Only pemberi_tugas or admin can delete
            $currentUserId = \Illuminate\Support\Facades\Auth::id();
            if ($tugasHarian->pemberi_tugas_id !== $currentUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menghapus tugas ini'
                ], 403);
            }

            $tugasHarian->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tugas harian berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update status tugas harian
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,dikerjakan,validasi,revisi,selesai'
        ]);

        $tugasHarian = \Modules\Penugasan\Models\TugasHarian::findOrFail($id);
        $tugasHarian->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status tugas harian berhasil diperbarui'
        ]);
    }

    /**
     * Update progress tugas harian
     */
    public function updateProgress(Request $request, $id)
    {
        $validated = $request->validate([
            'progress_persen' => 'required|numeric|min:0|max:100',
            'deskripsi_kegiatan' => 'required|string',
            'kendala' => 'nullable|string',
        ]);

        $tugasHarian = \Modules\Penugasan\Models\TugasHarian::findOrFail($id);

        // Create progress record dengan polymorphic
        \Modules\Penugasan\Models\Progress::create([
            'tipe_progress' => \Modules\Penugasan\Models\TugasHarian::class,
            'tipe_progress_id' => $tugasHarian->id,
            'pegawai_id' => $tugasHarian->pegawai_id,
            'tanggal' => now(),
            'progress_persen' => $validated['progress_persen'],
            'deskripsi_kegiatan' => $validated['deskripsi_kegiatan'],
            'kendala' => $validated['kendala'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Progress tugas berhasil diperbarui'
        ]);
    }

    /**
     * Get revision history for tugas harian
     */
    public function getHistory($id)
    {
        try {
            $tugasHarian = \Modules\Penugasan\Models\TugasHarian::findOrFail($id);

            // Get history revisi dengan polymorphic relation
            $history = [];
            if (class_exists('\Modules\Penugasan\Models\HistoriRevisi')) {
                $history = \Modules\Penugasan\Models\HistoriRevisi::with(['direvisiOleh', 'attachedFiles'])
                    ->where('tipe_revisi', \Modules\Penugasan\Models\TugasHarian::class)
                    ->where('tipe_revisi_id', $id)
                    ->orderBy('revisi_ke', 'desc')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'revisi_ke' => $item->revisi_ke,
                            'tanggal_revisi' => $item->tanggal_revisi,
                            'catatan_revisi' => $item->catatan_revisi,
                            'deadline_revisi' => $item->deadline_revisi,
                            'status' => $item->status,
                            'direvisi_oleh' => $item->direvisiOleh ? [
                                'id' => $item->direvisiOleh->id,
                                'nama' => $item->direvisiOleh->nama
                            ] : null,
                            'files' => $item->attachedFiles->map(function ($file) {
                                return [
                                    'id' => $file->id,
                                    'name' => $file->name,
                                    'original_name' => $file->original_name,
                                ];
                            })
                        ];
                    });
            }

            return response()->json([
                'success' => true,
                'history' => $history,
                'tugas' => [
                    'id' => $tugasHarian->id,
                    'nama_tugas' => $tugasHarian->nama_tugas,
                    'status' => $tugasHarian->status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat history revisi: ' . $e->getMessage(),
                'history' => []
            ], 500);
        }
    }
}

<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterPegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Penugasan\Models\TugasHarian;
use Modules\Penugasan\Models\TugasPokok;

/**
 * TugasHarianController
 * 
 * Mengelola tugas harian yang merupakan breakdown dari Tugas Pokok
 * Tugas harian bisa dibuat mandiri (is_mandiri=true) atau diberikan atasan
 */
class TugasHarianController extends Controller
{
    use AuthorizesRequests;

    /**
     * Menampilkan daftar tugas harian (untuk atasan/admin)
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $kodeJabatan = $user->jabatan?->kode;

        // Cek akses: Admin, Kaban, Sekban, Kabid
        if (!in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID'])) {
            abort(403, 'Tidak memiliki akses ke halaman ini');
        }

        $query = TugasHarian::with([
            'tugasPokok:id,nama_tugas',
            'pegawai:id,nama,jabatan_id,bidang_id',
            'pegawai.jabatan:id,nama',
            'pegawai.bidang:id,nama',
            'pemberiTugas:id,nama'
        ]);

        // Filter berdasarkan role
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            // Lihat semua
        } elseif ($kodeJabatan === 'KABID') {
            // Lihat hanya bidangnya
            $query->whereHas('pegawai', function ($q) use ($user) {
                $q->where('bidang_id', $user->bidang_id);
            });
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan tugas pokok
        if ($request->filled('tugas_pokok_id')) {
            $query->where('tugas_pokok_id', $request->tugas_pokok_id);
        }

        // Filter berdasarkan bidang
        if ($request->filled('bidang_id')) {
            $query->whereHas('pegawai', function ($q) use ($request) {
                $q->where('bidang_id', $request->bidang_id);
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

        $tugasHarian = $query->paginate($request->get('per_page', 20));

        // Get filter options
        $pegawaiList = \App\Models\MasterPegawai::where('status_aktif', 'Aktif')
            ->orderBy('nama')
            ->get();

        $bidangList = \App\Models\MasterBidang::where('is_active', true)
            ->orderBy('nama')
            ->get();

        // Calculate statistics
        $statsQuery = TugasHarian::query();

        // Apply same role-based filter for stats
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            // All tugas
        } elseif ($kodeJabatan === 'KABID') {
            $statsQuery->whereHas('pegawai', function ($q) use ($user) {
                $q->where('bidang_id', $user->bidang_id);
            });
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'dikerjakan' => (clone $statsQuery)->where('status', 'dikerjakan')->count(),
            'revisi' => (clone $statsQuery)->where('status', 'revisi')->count(),
            'validasi' => (clone $statsQuery)->where('status', 'validasi')->count(),
            'selesai' => (clone $statsQuery)->where('status', 'selesai')->count(),
        ];

        return view('penugasan::tugas-harian.index', compact('tugasHarian', 'pegawaiList', 'bidangList', 'stats'));
    }

    /**
     * Menampilkan daftar tugas harian milik user yang sedang login
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function tugasSaya(Request $request)
    {
        $user = $request->user();

        $query = TugasHarian::where('pegawai_id', $user->id)
            ->with([
                'tugasPokok:id,nama_tugas,progress_persen',
                'pemberiTugas:id,nama'
            ]);

        // Filter berdasarkan status
        $status = $request->get('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Filter berdasarkan tugas pokok
        $tugasPokokId = $request->get('tugas_pokok_id');
        if ($tugasPokokId) {
            $query->where('tugas_pokok_id', $tugasPokokId);
        }

        // Sort by urgency
        $query->orderByRaw("
            CASE 
                WHEN status = 'revisi' THEN 1
                WHEN status = 'dikerjakan' THEN 2
                WHEN status = 'pending' THEN 3
                ELSE 4
            END
        ")->orderBy('tanggal_selesai', 'asc');

        $tugasHarian = $query->paginate(20);

        // Group by status untuk UI yang lebih baik
        $grouped = $tugasHarian->getCollection()->groupBy('status');

        // Get tugas pokok for filter dropdown
        $tugasPokokList = TugasPokok::where('pegawai_id', $user->id)
            ->select('id', 'nama_tugas')
            ->get();

        return view('penugasan::tugas-harian.tugas-saya', compact(
            'tugasHarian',
            'grouped',
            'tugasPokokList',
            'status'
        ));
    }

    /**
     * Menampilkan form untuk membuat tugas harian baru (tugas mandiri)
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $user = $request->user();

        // Get tugas pokok for dropdown
        $tugasPokokList = TugasPokok::where('pegawai_id', $user->id)
            ->where('status', '!=', 'selesai')
            ->select('id', 'nama_tugas', 'progress_persen')
            ->get();

        return view('penugasan::tugas-harian.create', compact('tugasPokokList'));
    }

    /**
     * Menyimpan tugas harian baru (tugas mandiri)
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:master_pegawai,id',
            'tugas_pokok_id' => 'required|exists:knj_tugas_pokok,id',
            'nama_tugas' => 'required|string|max:500',
            'deskripsi' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'target_value' => 'required|numeric|min:1',
            'satuan' => 'required|string|max:100',
            'nilai' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $user = $request->user();
            $kodeJabatan = $user->jabatan?->kode;
            $pegawaiId = $validated['pegawai_id'];

            // Jika pegawai_id sama dengan user, ini self-assignment
            $isSelfAssignment = ($pegawaiId == $user->id);

            // Validasi tugas pokok - harus milik pegawai yang ditugaskan
            $tugasPokok = TugasPokok::where('id', $validated['tugas_pokok_id'])
                ->where('pegawai_id', $pegawaiId)
                ->firstOrFail();

            // Validasi hak akses untuk memberikan tugas
            if (!$isSelfAssignment) {
                $pegawai = MasterPegawai::findOrFail($pegawaiId);
                $hasAccess = false;

                if (in_array($kodeJabatan, ['KABAN', 'SEKBAN'])) {
                    // KABAN/SEKBAN bisa memberi tugas ke semua pegawai (except GATEK)
                    $hasAccess = ($pegawai->jabatan?->kode !== 'GATEK');
                } elseif ($kodeJabatan === 'KABID') {
                    // KABID bisa memberi tugas ke pegawai di bidang yang sama
                    $hasAccess = ($pegawai->bidang_id === $user->bidang_id);
                } else {
                    // Role lain hanya bisa memberi tugas ke bawahan langsung
                    $hasAccess = ($pegawai->atasan_langsung_id === $user->id);
                }

                if (!$hasAccess) {
                    throw new \Exception('Anda tidak memiliki hak akses untuk memberikan tugas kepada pegawai ini');
                }
            }

            $tugasHarian = TugasHarian::create([
                'tugas_pokok_id' => $validated['tugas_pokok_id'],
                'pegawai_id' => $pegawaiId,
                'pemberi_tugas_id' => $user->id,
                'nama_tugas' => $validated['nama_tugas'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'target_value' => $validated['target_value'],
                'satuan' => $validated['satuan'],
                'nilai' => $validated['nilai'] ?? 0,
                'is_mandiri' => $isSelfAssignment,
                'status' => 'pending',
                'status_approval' => $isSelfAssignment ? 'pending' : null, // NULL untuk tugas dari atasan (tidak perlu approval)
            ]);

            DB::commit();

            $message = $isSelfAssignment
                ? 'Tugas harian berhasil dibuat dan menunggu persetujuan atasan'
                : 'Tugas harian berhasil diberikan kepada ' . $tugasPokok->pegawai->nama;

            // Handle both AJAX and regular form submission
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $tugasHarian
                ]);
            }

            return redirect()->route('penugasan.tim.form-berikan-tugas')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat tugas harian: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gagal membuat tugas harian: ' . $e->getMessage())
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Menampilkan detail tugas harian
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $id)
    {
        $tugasHarian = TugasHarian::with([
            'tugasPokok:id,nama_tugas,progress_persen',
            'pegawai:id,nama,nomor_identitas,jabatan_id,bidang_id',
            'pegawai.jabatan:id,nama',
            'pegawai.bidang:id,nama',
            'pemberiTugas:id,nama',
            'validator:id,nama',
            'attachedFiles',
            'progress' => function ($q) {
                $q->orderBy('tanggal', 'desc')->limit(10);
            },
            'historyRevisi' => function ($q) {
                $q->with('direvisiOleh:id,nama')->orderBy('revisi_ke', 'desc');
            }
        ])->findOrFail($id);

        // Authorization check
        $this->authorize('view', $tugasHarian);

        return view('penugasan::tugas-harian.show', compact('tugasHarian'));
    }

    /**
     * Menampilkan form edit tugas harian
     * 
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            $tugasHarian = TugasHarian::with(['tugasPokok', 'pegawai', 'pemberiTugas'])
                ->findOrFail($id);

            $this->authorize('view', $tugasHarian);

            // Return JSON response for AJAX request
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json($tugasHarian);
            }

            return view('penugasan::tugas-harian.edit', compact('tugasHarian'));
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
     * Update tugas harian
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $tugasHarian = TugasHarian::findOrFail($id);
            $this->authorize('update', $tugasHarian);

            $validated = $request->validate([
                'nama_tugas' => 'required|string|max:500',
                'deskripsi' => 'nullable|string',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'target_value' => 'required|numeric|min:0',
                'satuan' => 'required|string|max:100',
            ]);

            $tugasHarian->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Tugas harian berhasil diperbarui'
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk melakukan aksi ini'
            ], 403);
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
     * Hapus tugas harian
     * 
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $tugasHarian = TugasHarian::findOrFail($id);
            $this->authorize('delete', $tugasHarian);

            $tugasHarian->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tugas harian berhasil dihapus'
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk melakukan aksi ini'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Terima tugas harian (dari pending ke dikerjakan)
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function terima(Request $request, $id)
    {
        $tugasHarian = TugasHarian::findOrFail($id);

        // Authorization: Hanya pemilik tugas yang bisa terima
        if ($tugasHarian->pegawai_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk melakukan aksi ini'
            ], 403);
        }

        if ($tugasHarian->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Tugas hanya bisa diterima jika berstatus pending'
            ], 422);
        }

        $tugasHarian->update([
            'status' => 'dikerjakan',
            'tanggal_mulai_aktual' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas harian berhasil diterima dan mulai dikerjakan'
        ]);
    }

    /**
     * Tolak tugas harian
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function tolak(Request $request, $id)
    {
        $validated = $request->validate([
            'alasan_penolakan' => 'required|string|max:1000'
        ]);

        $tugasHarian = TugasHarian::findOrFail($id);

        // Authorization: Hanya pemilik tugas yang bisa tolak
        if ($tugasHarian->pegawai_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk melakukan aksi ini'
            ], 403);
        }

        if ($tugasHarian->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya tugas berstatus pending yang bisa ditolak'
            ], 422);
        }

        // Soft delete dengan alasan
        $tugasHarian->update([
            'catatan_penolakan' => $validated['alasan_penolakan'],
            'ditolak_pada' => now()
        ]);
        $tugasHarian->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tugas harian berhasil ditolak'
        ]);
    }

    /**
     * Mulai mengerjakan tugas (deprecated - gunakan terima)
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function mulai(Request $request, $id)
    {
        return $this->terima($request, $id);
    }

    /**
     * Submit tugas untuk validasi
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit(Request $request, $id)
    {
        $tugasHarian = TugasHarian::findOrFail($id);

        // Authorization
        if ($tugasHarian->pegawai_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk melakukan aksi ini'
            ], 403);
        }

        // Cek status
        if (!in_array($tugasHarian->status, ['dikerjakan', 'revisi'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas hanya bisa disubmit jika sedang dikerjakan atau revisi'
            ], 422);
        }

        // Cek apakah ada bukti
        if ($tugasHarian->attachedFiles()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Harap upload bukti pengerjaan terlebih dahulu'
            ], 422);
        }

        $tugasHarian->update([
            'status' => 'validasi',
            'tanggal_submit' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil disubmit untuk validasi'
        ]);
    }

    /**
     * Menampilkan form upload bukti pengerjaan tugas
     * 
     * @param  string  $id
     * @return \Illuminate\View\View
     */
    public function formUploadBukti($id)
    {
        $tugas = TugasHarian::with([
            'tugasPokok:id,nama_tugas',
            'pegawai:id,nama'
        ])->findOrFail($id);

        // Authorization check - hanya pemilik tugas
        if ($tugas->pegawai_id !== request()->user()->id) {
            abort(403, 'Tidak memiliki akses');
        }

        $jenisTugas = 'tugas_harian';

        return view('penugasan::penugasan.upload-eviden', compact('tugas', 'jenisTugas'));
    }

    /**
     * Upload bukti pengerjaan tugas
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadBukti(Request $request, $id)
    {
        // Delegate to PenugasanController for now
        $controller = new \Modules\Penugasan\Http\Controllers\PenugasanController();
        return $controller->uploadBukti($request);
    }

    /**
     * Update progress tugas harian
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProgress(Request $request, $id)
    {
        $validated = $request->validate([
            'progress_persen' => 'required|numeric|min:0|max:100',
            'deskripsi_kegiatan' => 'required|string',
            'kendala' => 'nullable|string',
        ]);

        $tugasHarian = TugasHarian::findOrFail($id);

        // Create progress record dengan polymorphic
        \Modules\Penugasan\Models\Progress::create([
            'tipe_progress' => TugasHarian::class,
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
     * Mendapatkan history revisi tugas harian
     * 
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function history($id)
    {
        try {
            $tugasHarian = TugasHarian::findOrFail($id);

            // Get history revisi dengan polymorphic relation
            $history = [];
            if (class_exists('\Modules\Penugasan\Models\HistoriRevisi')) {
                $history = \Modules\Penugasan\Models\HistoriRevisi::with(['direvisiOleh', 'attachedFiles'])
                    ->where('tipe_revisi', TugasHarian::class)
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

    /**
     * Menampilkan tugas harian berdasarkan tugas pokok (nested route)
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $tugasPokokId
     * @return \Illuminate\View\View
     */
    public function indexByTugasPokok(Request $request, $tugasPokokId)
    {
        $tugasPokok = TugasPokok::findOrFail($tugasPokokId);

        // Authorization check
        $this->authorize('view', $tugasPokok);

        $tugasHarian = TugasHarian::where('tugas_pokok_id', $tugasPokokId)
            ->with([
                'pemberiTugas:id,nama',
                'validator:id,nama'
            ])
            ->orderBy('tanggal_mulai', 'desc')
            ->paginate(20);

        return view('penugasan::tugas-harian.by-tugas-pokok', compact('tugasPokok', 'tugasHarian'));
    }
}

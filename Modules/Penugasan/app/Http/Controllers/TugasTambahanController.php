<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\Penugasan\Models\TugasTambahan;

/**
 * TugasTambahanController
 *
 * Mengelola tugas tambahan (standalone, tidak terkait PK)
 * Tugas tambahan bisa diberikan oleh atasan ke bawahan (termasuk lintas bidang untuk level tertentu)
 * Kontribusi: 20-30% dari total penilaian kinerja
 */
class TugasTambahanController extends Controller
{
    use AuthorizesRequests;

    /**
     * Menampilkan daftar tugas tambahan (untuk atasan/admin)
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $kodeJabatan = $user->profile?->jabatan?->kode;

        // Cek akses: Admin, Kaban, Sekban, Kabid
        if (! in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID', 'KASUBAG'])) {
            abort(403, 'Tidak memiliki akses ke halaman ini');
        }

        $query = TugasTambahan::with([
            'pegawai:id,nama',
            'pegawai.profile:id,user_id,jabatan_id,bidang_id',
            'pegawai.profile.jabatan:id,nama',
            'pegawai.profile.bidang:id,nama',
            'pemberiTugas:id,nama',
            'validator:id,nama',
        ]);

        // Filter berdasarkan role
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            // Lihat semua
        } elseif ($kodeJabatan === 'KABID') {
            // Lihat hanya bidangnya
            $query->whereHas('pegawai', function ($q) use ($user) {
                $q->whereRelation('profile', 'bidang_id', $user->profile?->bidang_id);
            });
        } elseif ($kodeJabatan === 'KASUBAG') {
            // Lihat tugas yang diberikan sendiri atau untuk bawahannya
            $query->where(function ($q) use ($user) {
                $q->where('pemberi_tugas_id', $user->id)
                    ->orWhereHas('pegawai', function ($subQ) use ($user) {
                        $subQ->whereRelation('profile', 'atasan_langsung_id', $user->id);
                    });
            });
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan hasil validasi
        if ($request->filled('hasil_validasi')) {
            $query->where('hasil_validasi', $request->hasil_validasi);
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

        $tugasTambahan = $query->paginate($request->get('per_page', 20));

        // Get filter options
        $pegawaiList = \App\Models\User::whereRelation('profile', 'status_aktif', 'Aktif')
            ->orderBy('nama')
            ->get();

        // Calculate statistics
        $statsQuery = TugasTambahan::query();

        // Apply same role-based filter for stats
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            // All tugas
        } elseif ($kodeJabatan === 'KABID') {
            $statsQuery->whereHas('pegawai', function ($q) use ($user) {
                $q->whereRelation('profile', 'bidang_id', $user->profile?->bidang_id);
            });
        } elseif ($kodeJabatan === 'KASUBAG') {
            $statsQuery->where(function ($q) use ($user) {
                $q->where('pemberi_tugas_id', $user->id)
                    ->orWhereHas('pegawai', function ($subQ) use ($user) {
                        $subQ->whereRelation('profile', 'atasan_langsung_id', $user->id);
                    });
            });
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'dikerjakan' => (clone $statsQuery)->where('status', 'dikerjakan')->count(),
            'revisi' => (clone $statsQuery)->where('status', 'revisi')->count(),
            'validasi' => (clone $statsQuery)->where('status', 'validasi')->count(),
            'selesai' => (clone $statsQuery)->where('status', 'selesai')->count(),
            'lintas_bidang' => 0, // Feature not implemented yet
            'terlambat' => (clone $statsQuery)->where('tanggal_selesai', '<', now())
                ->whereNotIn('status', ['selesai'])->count(),
            'mendesak' => (clone $statsQuery)->whereBetween('tanggal_selesai', [now(), now()->addDays(3)])
                ->whereNotIn('status', ['selesai'])->count(),
        ];

        return view('penugasan::tugas-tambahan.index', compact('tugasTambahan', 'pegawaiList', 'stats'));
    }

    /**
     * Show form for creating new tugas tambahan
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $kodeJabatan = $user->profile?->jabatan?->kode;

        // Only atasan can create tugas tambahan
        if (! in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID', 'KASUBAG'])) {
            abort(403, 'Tidak memiliki akses untuk membuat tugas tambahan');
        }

        // Get pegawai list based on role
        $pegawaiQuery = \App\Models\User::whereRelation('profile', 'status_aktif', 'Aktif')
            ->where('id', '!=', $user->id); // Exclude self

        if ($kodeJabatan === 'KABID') {
            // Only pegawai in same bidang
            $pegawaiQuery->whereRelation('profile', 'bidang_id', $user->profile?->bidang_id);
        } elseif ($kodeJabatan === 'KASUBAG') {
            // Only direct subordinates
            $pegawaiQuery->whereRelation('profile', 'atasan_langsung_id', $user->id);
        }

        $pegawaiList = $pegawaiQuery->with(['profile.jabatan', 'profile.bidang'])->orderBy('nama')->get();

        return view('penugasan::tugas-tambahan.create', compact('pegawaiList'));
    }

    /**
     * Menampilkan daftar tugas tambahan milik user yang sedang login
     *
     * @return \Illuminate\View\View
     */
    public function tugasSaya(Request $request)
    {
        $user = $request->user();

        $query = TugasTambahan::where('pegawai_id', $user->id)
            ->with([
                'pemberiTugas:id,nama',
                'validator:id,nama',
            ]);

        // Filter berdasarkan status
        $status = $request->get('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Filter berdasarkan hasil validasi
        $hasilValidasi = $request->get('hasil_validasi');
        if ($hasilValidasi) {
            $query->where('hasil_validasi', $hasilValidasi);
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

        $tugasTambahan = $query->paginate(20);

        // Group by status untuk UI yang lebih baik
        $grouped = $tugasTambahan->getCollection()->groupBy('status');

        return view('penugasan::tugas-tambahan.tugas-saya', compact(
            'tugasTambahan',
            'grouped',
            'status'
        ));
    }

    /**
     * Simpan tugas tambahan baru (diberikan oleh atasan ke bawahan)
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'pegawai_id' => 'required|exists:users,id',
                'nama_tugas' => 'required|string|max:500',
                'deskripsi' => 'nullable|string',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'alasan_penugasan' => 'nullable|string',
                'target_penilaian' => 'nullable|numeric|min:0|max:100',
            ]);

            $user = $request->user();
            $pegawai = User::findOrFail($validated['pegawai_id']);
            $kodeJabatan = $user->profile?->jabatan?->kode;

            // Cek hak akses berdasarkan role
            $hasAccess = false;

            if (in_array($kodeJabatan, ['KABAN', 'SEKBAN'])) {
                // KABAN/SEKBAN bisa memberi tugas ke semua pegawai (except self)
                $hasAccess = ($pegawai->id !== $user->id);
            } elseif ($kodeJabatan === 'KABID') {
                // KABID bisa memberi tugas ke pegawai di bidang yang sama atau GATEK
                $hasAccess = ($pegawai->profile?->bidang_id === $user->bidang_id) ||
                    ($pegawai->profile?->jabatan?->kode === 'GATEK');
            } elseif ($kodeJabatan === 'KASUBAG') {
                // KASUBAG bisa memberi tugas ke bawahan langsung atau GATEK
                $hasAccess = ($pegawai->profile?->atasan_langsung_id === $user->id) ||
                    ($pegawai->profile?->jabatan?->kode === 'GATEK');
            } else {
                // Role lain hanya bisa memberi tugas ke bawahan langsung
                $hasAccess = ($pegawai->profile?->atasan_langsung_id === $user->id);
            }

            if (! $hasAccess) {
                return redirect()->back()
                    ->with('error', 'Anda tidak memiliki hak akses untuk memberikan tugas kepada pegawai ini')
                    ->withInput();
            }

            // Create tugas tambahan
            $tugasTambahan = TugasTambahan::create([
                'pegawai_id' => $validated['pegawai_id'],
                'pemberi_tugas_id' => $user->id,
                'nama_tugas' => $validated['nama_tugas'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'alasan_penugasan' => $validated['alasan_penugasan'] ?? null,
                'target_penilaian' => $validated['target_penilaian'] ?? null,
                'status' => 'pending',
            ]);

            return redirect()->route('penugasan.tim.form-berikan-tugas')
                ->with('success', 'Tugas tambahan berhasil diberikan kepada '.$pegawai->nama);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Menampilkan detail tugas tambahan
     *
     * @param  string  $id
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $id)
    {
        $tugasTambahan = TugasTambahan::with([
            'pegawai:id,nama',
            'pegawai.profile:id,user_id,jabatan_id,bidang_id',
            'pegawai.profile.jabatan:id,nama',
            'pegawai.profile.bidang:id,nama',
            'pemberiTugas:id,nama,jabatan_id',
            'pemberiTugas.jabatan:id,nama',
            'validator:id,nama',
            'attachedFiles',
            'progress' => function ($q) {
                $q->orderBy('tanggal', 'desc')->limit(10);
            },
            'historyRevisi' => function ($q) {
                $q->with('direvisiOleh:id,nama')->orderBy('revisi_ke', 'desc');
            },
        ])->findOrFail($id);

        // Authorization check
        $this->authorize('view', $tugasTambahan);

        return view('penugasan::tugas-tambahan.show', compact('tugasTambahan'));
    }

    /**
     * Menampilkan form edit tugas tambahan
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            $tugasTambahan = TugasTambahan::with(['pegawai', 'pemberiTugas', 'validator', 'attachedFiles'])
                ->findOrFail($id);

            $this->authorize('view', $tugasTambahan);

            // Return JSON response for AJAX request
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json($tugasTambahan);
            }

            return view('penugasan::tugas-tambahan.edit', compact('tugasTambahan'));
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas tambahan tidak ditemukan',
                ], 404);
            }

            return redirect()->back()->with('error', 'Tugas tambahan tidak ditemukan');
        }
    }

    /**
     * Update tugas tambahan
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $tugasTambahan = TugasTambahan::findOrFail($id);
            $this->authorize('update', $tugasTambahan);

            $validated = $request->validate([
                'nama_tugas' => 'required|string|max:500',
                'deskripsi' => 'nullable|string',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'target_value' => 'required|numeric|min:1',
                'satuan' => 'required|string|max:50',
            ]);

            $tugasTambahan->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Tugas tambahan berhasil diperbarui',
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk melakukan aksi ini',
            ], 403);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus tugas tambahan
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $tugasTambahan = TugasTambahan::findOrFail($id);
            $this->authorize('delete', $tugasTambahan);

            $tugasTambahan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tugas tambahan berhasil dihapus',
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk melakukan aksi ini',
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Terima tugas tambahan (dari pending ke dikerjakan)
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function terima(Request $request, $id)
    {
        $tugasTambahan = TugasTambahan::findOrFail($id);

        // Authorization: Hanya pemilik tugas yang bisa terima
        if ($tugasTambahan->pegawai_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk melakukan aksi ini',
            ], 403);
        }

        if ($tugasTambahan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Tugas hanya bisa diterima jika berstatus pending',
            ], 422);
        }

        $tugasTambahan->update([
            'status' => 'dikerjakan',
            'tanggal_mulai_aktual' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas tambahan berhasil diterima dan mulai dikerjakan',
        ]);
    }

    /**
     * Tolak tugas tambahan
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function tolak(Request $request, $id)
    {
        $validated = $request->validate([
            'alasan_penolakan' => 'required|string|max:1000',
        ]);

        $tugasTambahan = TugasTambahan::findOrFail($id);

        // Authorization: Hanya pemilik tugas yang bisa tolak
        if ($tugasTambahan->pegawai_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk melakukan aksi ini',
            ], 403);
        }

        if ($tugasTambahan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya tugas berstatus pending yang bisa ditolak',
            ], 422);
        }

        // Soft delete dengan alasan
        $tugasTambahan->update([
            'catatan_penolakan' => $validated['alasan_penolakan'],
            'ditolak_pada' => now(),
        ]);
        $tugasTambahan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tugas tambahan berhasil ditolak',
        ]);
    }

    /**
     * Mulai mengerjakan tugas (deprecated - gunakan terima)
     *
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
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit(Request $request, $id)
    {
        $tugasTambahan = TugasTambahan::findOrFail($id);

        // Authorization
        if ($tugasTambahan->pegawai_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk melakukan aksi ini',
            ], 403);
        }

        // Cek status
        if (! in_array($tugasTambahan->status, ['dikerjakan', 'revisi'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas hanya bisa disubmit jika sedang dikerjakan atau revisi',
            ], 422);
        }

        // Cek apakah ada bukti
        if ($tugasTambahan->attachedFiles()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Harap upload bukti pengerjaan terlebih dahulu',
            ], 422);
        }

        $tugasTambahan->update([
            'status' => 'validasi',
            'tanggal_submit' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil disubmit untuk validasi',
        ]);
    }

    /**
     * Upload bukti pengerjaan tugas
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadBukti(Request $request, $id)
    {
        // Delegate to PenugasanController for now
        $controller = new \Modules\Penugasan\Http\Controllers\PenugasanController;

        return $controller->uploadBukti($request);
    }

    /**
     * Update progress tugas tambahan
     *
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

        $tugasTambahan = TugasTambahan::findOrFail($id);

        // Create progress record dengan polymorphic
        \Modules\Penugasan\Models\Progress::create([
            'tipe_progress' => TugasTambahan::class,
            'tipe_progress_id' => $tugasTambahan->id,
            'pegawai_id' => $tugasTambahan->pegawai_id,
            'tanggal' => now(),
            'progress_persen' => $validated['progress_persen'],
            'deskripsi_kegiatan' => $validated['deskripsi_kegiatan'],
            'kendala' => $validated['kendala'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Progress tugas berhasil diperbarui',
        ]);
    }
}

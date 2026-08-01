<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterBidang;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\Penugasan\Models\HistoriRevisi;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Models\Progress;

/**
 * PenugasanController (web)
 *
 * Menggantikan TugasPokokController + TugasHarianController + TugasTambahanController
 * (dan god-controller PenugasanController lama) setelah ketiga konsep tersebut
 * digabung menjadi satu entitas Penugasan. Field `jenis` (pokok/tambahan)
 * dipakai sebagai label klasifikasi/filter, bukan lagi controller/tabel terpisah.
 *
 * Catatan: view Blade di bawah direktori tugas-pokok/tugas-harian/tugas-tambahan
 * masih menunggu penyesuaian penuh ke skema baru pada sesi lanjutan — controller
 * ini sudah query benar terhadap skema baru, tapi sebagian view mungkin masih
 * perlu perbaikan referensi field/relasi.
 */
class PenugasanController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, ?string $jenis = null)
    {
        $user = $request->user();
        $kodeJabatan = $user->profile?->jabatan?->kode;

        if (! in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID', 'KASUBAG'])) {
            abort(403, 'Tidak memiliki akses ke halaman ini');
        }

        $query = Penugasan::with([
            'pegawai:id,nama',
            'pegawai.profile:id,user_id,jabatan_id,bidang_id',
            'pegawai.profile.jabatan:id,nama',
            'pegawai.profile.bidang:id,nama',
            'pemberiTugas:id,nama',
            'validator:id,nama',
        ]);

        if ($jenis) {
            $query->where('jenis', $jenis);
        }

        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            // Lihat semua
        } elseif ($kodeJabatan === 'KABID') {
            $query->whereHas('pegawai', function ($q) use ($user) {
                $q->whereRelation('profile', 'bidang_id', $user->profile?->bidang_id);
            });
        } elseif ($kodeJabatan === 'KASUBAG') {
            $query->where(function ($q) use ($user) {
                $q->where('pemberi_tugas_id', $user->id)
                    ->orWhereHas('pegawai', function ($subQ) use ($user) {
                        $subQ->whereRelation('profile', 'atasan_langsung_id', $user->id);
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('bidang_id')) {
            $query->whereHas('pegawai', function ($q) use ($request) {
                $q->whereRelation('profile', 'bidang_id', $request->bidang_id);
            });
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_tugas', 'like', "%{$request->search}%")
                    ->orWhere('deskripsi', 'like', "%{$request->search}%")
                    ->orWhereHas('pegawai', function ($subQ) use ($request) {
                        $subQ->where('nama', 'like', "%{$request->search}%");
                    });
            });
        }

        $sortBy = $request->get('sort_by', 'tanggal_mulai');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $penugasan = $query->paginate($request->get('per_page', 20));

        $pegawaiList = User::whereRelation('profile', 'status_aktif', 'Aktif')->orderBy('nama')->get();
        $bidangList = MasterBidang::where('is_active', true)->orderBy('nama')->get();

        $statsQuery = Penugasan::query();
        if ($jenis) {
            $statsQuery->where('jenis', $jenis);
        }
        if ($kodeJabatan === 'KABID') {
            $statsQuery->whereHas('pegawai', function ($q) use ($user) {
                $q->whereRelation('profile', 'bidang_id', $user->profile?->bidang_id);
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

        return view('penugasan::penugasan.daftar', compact('penugasan', 'pegawaiList', 'bidangList', 'stats', 'jenis'));
    }

    public function tugasSaya(Request $request, ?string $jenis = null)
    {
        $user = $request->user();

        $query = Penugasan::where('pegawai_id', $user->id)
            ->with(['pemberiTugas:id,nama', 'validator:id,nama']);

        if ($jenis) {
            $query->where('jenis', $jenis);
        }

        $status = $request->get('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $query->orderByRaw("
            CASE
                WHEN status = 'revisi' THEN 1
                WHEN status = 'dikerjakan' THEN 2
                WHEN status = 'pending' THEN 3
                ELSE 4
            END
        ")->orderBy('tanggal_selesai', 'asc');

        $penugasan = $query->paginate(20);
        $grouped = $penugasan->getCollection()->groupBy('status');

        return view('penugasan::penugasan.tugas-saya', compact('penugasan', 'grouped', 'status', 'jenis'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $this->authorize('create', Penugasan::class);

        $pegawaiList = User::whereRelation('profile', 'status_aktif', 'Aktif')
            ->where('id', '!=', $user->id)
            ->with(['profile.jabatan', 'profile.bidang'])
            ->orderBy('nama')
            ->get();

        return view('penugasan::penugasan.create', compact('pegawaiList'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'pegawai_id' => 'required|exists:users,id',
                'jenis' => 'required|in:'.implode(',', Penugasan::JENISES),
                'nama_tugas' => 'required|string|max:255',
                'deskripsi' => 'required|string',
                'alasan_penugasan' => 'nullable|string',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'target_value' => 'nullable|numeric|min:0',
                'satuan' => 'nullable|string|max:30',
                'bobot_persen' => 'required|numeric|min:0|max:100',
            ]);

            $user = $request->user();
            $isSelfInitiated = ((int) $validated['pegawai_id']) === $user->id;

            if (! $isSelfInitiated) {
                $this->authorize('create', Penugasan::class);
                $target = User::findOrFail($validated['pegawai_id']);
                $this->authorize('assignTo', [Penugasan::class, $target]);
            }

            $penugasan = Penugasan::create([
                ...$validated,
                'pemberi_tugas_id' => $isSelfInitiated ? null : $user->id,
                'status' => Penugasan::STATUS_PENDING,
                'status_approval' => $isSelfInitiated ? Penugasan::APPROVAL_PENDING : null,
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Penugasan berhasil dibuat', 'data' => $penugasan], 201);
            }

            return redirect()->route('penugasan.tim.form-berikan-tugas')
                ->with('success', 'Penugasan berhasil diberikan kepada '.User::find($validated['pegawai_id'])?->nama);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak memiliki hak akses untuk memberikan tugas kepada pegawai ini'], 403);
            }

            return redirect()->back()->with('error', 'Tidak memiliki hak akses untuk memberikan tugas kepada pegawai ini')->withInput();
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }

    public function show(Request $request, string $id)
    {
        $penugasan = Penugasan::with([
            'pegawai:id,nama',
            'pegawai.profile:id,user_id,jabatan_id,bidang_id',
            'pegawai.profile.jabatan:id,nama',
            'pegawai.profile.bidang:id,nama',
            'pemberiTugas:id,nama',
            'validator:id,nama',
            'attachedFiles',
            'progress' => fn ($q) => $q->orderByDesc('tanggal')->limit(10),
            'historyRevisi' => fn ($q) => $q->with('direvisiOleh:id,nama')->orderByDesc('revisi_ke'),
        ])->findOrFail($id);

        $this->authorize('view', $penugasan);

        return view('penugasan::penugasan.detail', compact('penugasan'));
    }

    public function edit(string $id)
    {
        $penugasan = Penugasan::with(['pegawai', 'pemberiTugas'])->findOrFail($id);
        $this->authorize('update', $penugasan);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($penugasan);
        }

        return view('penugasan::penugasan.edit', compact('penugasan'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $this->authorize('update', $penugasan);

            $validated = $request->validate([
                'nama_tugas' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'target_value' => 'nullable|numeric|min:0',
                'satuan' => 'nullable|string|max:30',
                'bobot_persen' => 'sometimes|numeric|min:0|max:100',
            ]);

            $penugasan->update($validated);

            return response()->json(['success' => true, 'message' => 'Penugasan berhasil diperbarui']);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        }
    }

    public function destroy(string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $this->authorize('delete', $penugasan);
            $penugasan->delete();

            return response()->json(['success' => true, 'message' => 'Penugasan berhasil dihapus']);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        }
    }

    public function terima(Request $request, string $id)
    {
        $penugasan = Penugasan::findOrFail($id);

        if ($request->user()->id !== $penugasan->pegawai_id) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        }

        if ($penugasan->status !== Penugasan::STATUS_PENDING) {
            return response()->json(['success' => false, 'message' => 'Penugasan hanya bisa diterima jika berstatus pending'], 422);
        }

        $penugasan->update(['status' => Penugasan::STATUS_DIKERJAKAN]);

        return response()->json(['success' => true, 'message' => 'Penugasan diterima dan mulai dikerjakan']);
    }

    public function tolak(Request $request, string $id)
    {
        $validated = $request->validate(['alasan_penolakan' => 'required|string|max:1000']);
        $penugasan = Penugasan::findOrFail($id);

        if ($request->user()->id !== $penugasan->pegawai_id) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        }

        if ($penugasan->status !== Penugasan::STATUS_PENDING) {
            return response()->json(['success' => false, 'message' => 'Hanya penugasan berstatus pending yang bisa ditolak'], 422);
        }

        $penugasan->update(['alasan_reject' => $validated['alasan_penolakan']]);
        $penugasan->delete();

        return response()->json(['success' => true, 'message' => 'Penugasan berhasil ditolak']);
    }

    public function mulai(Request $request, string $id)
    {
        return $this->terima($request, $id);
    }

    /**
     * Alias untuk store() — dipakai TeamController::berikanTugas() (form "Berikan Tugas").
     */
    public function berikanTugas(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Atasan memvalidasi tugas: menetapkan realisasi_persen, menghitung
     * nilai_akhir = bobot_persen x realisasi_persen / 100.
     */
    public function validasiTugas(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $this->authorize('validasi', $penugasan);

            $validated = $request->validate([
                'status_validasi' => 'required|in:diterima,revisi,ditolak',
                'realisasi_persen' => 'required_if:status_validasi,diterima|nullable|numeric|min:0|max:100',
                'catatan_validasi' => 'nullable|string',
            ]);

            $update = [
                'hasil_validasi' => $validated['status_validasi'],
                'catatan_validasi' => $validated['catatan_validasi'] ?? null,
                'validator_id' => $request->user()->id,
                'validated_at' => now(),
            ];

            if ($validated['status_validasi'] === Penugasan::VALIDASI_DITERIMA) {
                $update['realisasi_persen'] = $validated['realisasi_persen'];
                $update['status'] = Penugasan::STATUS_SELESAI;
                $update['diterima_at'] = $penugasan->diterima_at ?? now();
            } elseif ($validated['status_validasi'] === Penugasan::VALIDASI_REVISI) {
                $update['status'] = Penugasan::STATUS_REVISI;

                $revisiKe = HistoriRevisi::where('penugasan_id', $penugasan->id)->max('revisi_ke');
                HistoriRevisi::create([
                    'penugasan_id' => $penugasan->id,
                    'revisi_ke' => ($revisiKe ?? 0) + 1,
                    'tanggal_revisi' => now(),
                    'catatan_revisi' => $validated['catatan_validasi'] ?? '',
                    'deadline_revisi' => now()->addDays(3),
                    'direvisi_oleh' => $request->user()->id,
                    'pegawai_id' => $penugasan->pegawai_id,
                ]);
            } else {
                $update['status'] = Penugasan::STATUS_DIKERJAKAN;
            }

            $penugasan->update($update);

            if ($penugasan->status === Penugasan::STATUS_SELESAI) {
                $penugasan->update(['nilai_akhir' => $penugasan->hitungNilaiAkhir()]);
            }

            return response()->json(['success' => true, 'message' => 'Validasi berhasil disimpan', 'data' => $penugasan->fresh()]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        }
    }

    /**
     * Preview nilai_akhir = bobot_persen x realisasi_persen / 100 sebelum disimpan.
     */
    public function previewPenilaian(Request $request)
    {
        $validated = $request->validate([
            'bobot_persen' => 'required|numeric|min:0|max:100',
            'realisasi_persen' => 'required|numeric|min:0|max:100',
        ]);

        $nilaiAkhir = round(($validated['bobot_persen'] * $validated['realisasi_persen']) / 100, 2);

        return response()->json([
            'success' => true,
            'data' => [
                'bobot_persen' => $validated['bobot_persen'],
                'realisasi_persen' => $validated['realisasi_persen'],
                'nilai_akhir' => $nilaiAkhir,
            ],
        ]);
    }

    public function submit(Request $request, string $id)
    {
        $penugasan = Penugasan::findOrFail($id);

        if ($request->user()->id !== $penugasan->pegawai_id) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        }

        if (! in_array($penugasan->status, [Penugasan::STATUS_DIKERJAKAN, Penugasan::STATUS_REVISI])) {
            return response()->json(['success' => false, 'message' => 'Penugasan hanya bisa disubmit jika sedang dikerjakan atau revisi'], 422);
        }

        if ($penugasan->attachedFiles()->count() === 0) {
            return response()->json(['success' => false, 'message' => 'Harap upload bukti pengerjaan terlebih dahulu'], 422);
        }

        $penugasan->update(['status' => Penugasan::STATUS_VALIDASI]);

        return response()->json(['success' => true, 'message' => 'Penugasan berhasil disubmit untuk validasi']);
    }

    public function formUploadBukti(string $id)
    {
        $tugas = Penugasan::with(['pegawai:id,nama'])->findOrFail($id);

        if ($tugas->pegawai_id !== request()->user()->id) {
            abort(403, 'Tidak memiliki akses');
        }

        return view('penugasan::penugasan.upload-eviden', ['tugas' => $tugas, 'jenisTugas' => $tugas->jenis]);
    }

    public function updateProgress(Request $request, string $id)
    {
        $validated = $request->validate([
            'progress_persen' => 'required|numeric|min:0|max:100',
            'deskripsi_kegiatan' => 'required|string',
            'kendala' => 'nullable|string',
        ]);

        $penugasan = Penugasan::findOrFail($id);

        Progress::create([
            'penugasan_id' => $penugasan->id,
            'pegawai_id' => $penugasan->pegawai_id,
            'tanggal' => now()->toDateString(),
            'progress_persen' => $validated['progress_persen'],
            'deskripsi_kegiatan' => $validated['deskripsi_kegiatan'],
            'kendala' => $validated['kendala'] ?? null,
        ]);

        $penugasan->update(['progress_persen' => $validated['progress_persen']]);

        return response()->json(['success' => true, 'message' => 'Progress tugas berhasil diperbarui']);
    }

    public function history(string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);

            $history = HistoriRevisi::with(['direvisiOleh', 'attachedFiles'])
                ->where('penugasan_id', $id)
                ->orderByDesc('revisi_ke')
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'revisi_ke' => $item->revisi_ke,
                    'tanggal_revisi' => $item->tanggal_revisi,
                    'catatan_revisi' => $item->catatan_revisi,
                    'deadline_revisi' => $item->deadline_revisi,
                    'status' => $item->status,
                    'direvisi_oleh' => $item->direvisiOleh ? ['id' => $item->direvisiOleh->id, 'nama' => $item->direvisiOleh->nama] : null,
                    'files' => $item->attachedFiles->map(fn ($file) => ['id' => $file->id, 'name' => $file->name, 'original_name' => $file->original_name]),
                ]);

            return response()->json([
                'success' => true,
                'history' => $history,
                'tugas' => ['id' => $penugasan->id, 'nama_tugas' => $penugasan->nama_tugas, 'status' => $penugasan->status],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memuat history revisi: '.$e->getMessage(), 'history' => []], 500);
        }
    }
}

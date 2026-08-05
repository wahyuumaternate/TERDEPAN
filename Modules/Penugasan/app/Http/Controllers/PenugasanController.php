<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterBidang;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Models\PerpanjanganWaktu;
use Modules\Penugasan\Services\AtasanMandiriEligibility;
use Modules\Penugasan\Services\PenugasanActionService;
use Modules\TerminalData\Models\TdFile;
use Modules\TerminalData\Models\TdFolder;

/**
 * PenugasanController (web)
 *
 * Mengikuti docs/plan/08-rencana_implementasi_tampilan_web_penugasan.md:
 * "Tugas Saya" dan "Tugas yang Saya Berikan" digabung dalam tugasSaya() lewat
 * query param ?tab=. Seluruh logic aksi (terima/tolak/nilai/dst) didelegasikan
 * ke PenugasanActionService yang sama dipakai Api\PenugasanController, supaya
 * web tidak lagi drift dari aturan bisnis di API (dok. 08 §4.2).
 */
class PenugasanController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly PenugasanActionService $actionService) {}

    /**
     * Daftar penugasan lintas-organisasi untuk keperluan manajemen (Kaban/Sekban/Kabid/Kasubag).
     * Berbeda dari tugasSaya() yang berbasis "milik saya" — halaman ini scope-nya luas.
     */
    public function index(Request $request)
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

        $jenis = $request->get('jenis');
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

        $penugasan = $query->paginate($request->get('per_page', 20))->withQueryString();

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
            'pending' => (clone $statsQuery)->where('status', Penugasan::STATUS_PENDING)->count(),
            'proses' => (clone $statsQuery)->whereIn('status', [Penugasan::STATUS_PROSES, Penugasan::STATUS_REVISI])->count(),
            'terlambat' => (clone $statsQuery)->where('status', Penugasan::STATUS_TERLAMBAT)->count(),
            'menunggu_nilai' => (clone $statsQuery)->where('status', Penugasan::STATUS_SELESAI)->whereNull('realisasi_persen')->count(),
            'selesai' => (clone $statsQuery)->where('status', Penugasan::STATUS_SELESAI)->whereNotNull('realisasi_persen')->count(),
        ];

        return view('penugasan::penugasan.daftar', compact('penugasan', 'bidangList', 'stats', 'jenis'));
    }

    /**
     * Halaman gabungan "Tugas Saya" (tab=saya, default) dan
     * "Tugas yang Saya Berikan" (tab=diberikan) — dok. 08 §4.1.
     */
    public function tugasSaya(Request $request)
    {
        $user = $request->user();
        $bisaMemberi = Gate::forUser($user)->allows('create', Penugasan::class);
        $tab = $this->resolveTabTugasSaya($request, $bisaMemberi);

        $penugasan = $this->queryTugasSaya($request, $tab, $user)->paginate(12)->withQueryString();
        $stats = $this->statsTugasSaya($tab, $user);

        $perpanjanganMenunggu = collect();
        if ($tab === 'diberikan') {
            $perpanjanganMenunggu = PerpanjanganWaktu::where('status', PerpanjanganWaktu::STATUS_MENUNGGU)
                ->whereHas('penugasan', fn ($q) => $q->where('pemberi_tugas_id', $user->id))
                ->with(['penugasan:id,nama_tugas,pegawai_id', 'penugasan.pegawai:id,nama'])
                ->get();
        }

        $statusOptions = Penugasan::STATUSES;
        $prioritasOptions = Penugasan::PRIORITASES;

        return view('penugasan::penugasan.tugas-saya', compact(
            'penugasan', 'tab', 'bisaMemberi', 'statusOptions', 'prioritasOptions', 'perpanjanganMenunggu', 'stats'
        ));
    }

    /**
     * Endpoint AJAX yang di-polling berkala oleh halaman Tugas Saya (fetch tiap
     * beberapa detik) supaya tabel & statistik ringkas ter-update tanpa reload penuh.
     * Mengembalikan potongan HTML yang sama persis dengan render awal, satu sumber
     * markup/badge di partial `penugasan.partials.tugas-saya-tabel` — bukan
     * duplikasi logic warna status di JavaScript.
     */
    public function tugasSayaData(Request $request)
    {
        $user = $request->user();
        $bisaMemberi = Gate::forUser($user)->allows('create', Penugasan::class);
        $tab = $this->resolveTabTugasSaya($request, $bisaMemberi);

        $penugasan = $this->queryTugasSaya($request, $tab, $user)->paginate(12)->withQueryString();
        $stats = $this->statsTugasSaya($tab, $user);

        return view('penugasan::penugasan.partials.tugas-saya-tabel', compact('penugasan', 'tab', 'stats'));
    }

    private function resolveTabTugasSaya(Request $request, bool $bisaMemberi): string
    {
        $tab = $request->get('tab', 'saya');

        if (! in_array($tab, ['saya', 'diberikan'], true) || ($tab === 'diberikan' && ! $bisaMemberi)) {
            return 'saya';
        }

        return $tab;
    }

    private function queryTugasSaya(Request $request, string $tab, User $user)
    {
        if ($tab === 'diberikan') {
            $query = Penugasan::where('pemberi_tugas_id', $user->id)
                ->with([
                    'pegawai:id,nama',
                    'pegawai.profile:id,user_id,jabatan_id,bidang_id',
                    'pegawai.profile.jabatan:id,nama',
                    'validator:id,nama',
                ]);
        } else {
            $query = Penugasan::where('pegawai_id', $user->id)
                ->with(['pemberiTugas:id,nama', 'validator:id,nama']);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->string('jenis'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('prioritas')) {
            $query->where('prioritas', $request->string('prioritas'));
        }

        return match ($request->get('sort', 'urgensi')) {
            'prioritas' => $query->orderByRaw("
                CASE prioritas
                    WHEN 'tinggi' THEN 1
                    WHEN 'sedang' THEN 2
                    ELSE 3
                END
            ")->orderBy('deadline_terbaru', 'asc'),
            'terbaru' => $query->orderByDesc('created_at'),
            'nama' => $query->orderBy('nama_tugas'),
            // 'urgensi' (default): terlambat > revisi > proses > pending > sisanya (fix bug orderByRaw lama
            // yang memakai literal status 'dikerjakan' yang sudah tidak ada sejak rencana 07, lihat dok. 08 §3.3).
            default => $query->orderByRaw("
                CASE status
                    WHEN 'terlambat' THEN 1
                    WHEN 'revisi' THEN 2
                    WHEN 'proses' THEN 3
                    WHEN 'pending' THEN 4
                    ELSE 5
                END
            ")->orderBy('deadline_terbaru', 'asc'),
        };
    }

    /**
     * Statistik ringkas per status untuk tab aktif — sengaja dihitung dari seluruh data
     * tab (tidak ikut filter status/prioritas/jenis) supaya angka totalnya stabil sebagai
     * acuan, terlepas dari filter yang sedang diterapkan pada tabel di bawahnya.
     *
     * @return array<string, int>
     */
    private function statsTugasSaya(string $tab, User $user): array
    {
        $base = $tab === 'diberikan'
            ? Penugasan::where('pemberi_tugas_id', $user->id)
            : Penugasan::where('pegawai_id', $user->id);

        return [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', Penugasan::STATUS_PENDING)->count(),
            'proses' => (clone $base)->where('status', Penugasan::STATUS_PROSES)->count(),
            'revisi' => (clone $base)->where('status', Penugasan::STATUS_REVISI)->count(),
            'terlambat' => (clone $base)->where('status', Penugasan::STATUS_TERLAMBAT)->count(),
            'selesai' => (clone $base)->where('status', Penugasan::STATUS_SELESAI)->count(),
        ];
    }

    /**
     * Wizard pembuatan tugas — Jalur A (berikan ke pegawai lain/grup) & Jalur B (mandiri).
     * Kedua jalur dirender di satu halaman, disembunyikan sesuai hak akses role (dok. 08 §5.2, §5.5).
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $bisaMemberi = Gate::forUser($user)->allows('create', Penugasan::class);

        $calonPegawai = $bisaMemberi ? $this->calonPegawaiBisaDitugaskan($user) : collect();
        $atasanKandidat = app(AtasanMandiriEligibility::class)->kandidatUntuk($user);
        $pegawaiIdTerpilih = $request->integer('pegawai_id') ?: null;

        return view('penugasan::penugasan.create', compact('bisaMemberi', 'calonPegawai', 'atasanKandidat', 'pegawaiIdTerpilih'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate(PenugasanActionService::aturanBuat());
            $penugasan = $this->actionService->buat($validated, $request->user());

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Penugasan berhasil dibuat', 'data' => $penugasan], 201);
            }

            $tab = ((int) $validated['pegawai_id']) === $request->user()->id ? 'saya' : 'diberikan';

            return redirect()->route('penugasan.tugas-saya', ['tab' => $tab])
                ->with('success', 'Penugasan berhasil dibuat');
        } catch (AuthorizationException $e) {
            $message = $e->getMessage() ?: 'Tidak memiliki hak akses untuk memberikan tugas kepada pegawai ini';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }

            return redirect()->back()->with('error', $message)->withInput();
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Jalur A (grup): satu tugas ke lebih dari satu pegawai sekaligus (kolektif/per_orang).
     */
    public function storeGrup(Request $request)
    {
        try {
            $validated = $request->validate(PenugasanActionService::aturanBuatGrup());
            $this->actionService->buatGrup($validated, $request->user());

            return redirect()->route('penugasan.tugas-saya', ['tab' => 'diberikan'])
                ->with('success', 'Penugasan grup berhasil dibuat');
        } catch (AuthorizationException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage() ?: 'Tidak memiliki hak akses untuk memberikan tugas ke salah satu pegawai')
                ->withInput();
        } catch (ValidationException $e) {
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
            'progress' => fn ($q) => $q->orderByDesc('tanggal'),
            'historyRevisi' => fn ($q) => $q->with('direvisiOleh:id,nama')->orderByDesc('revisi_ke'),
            'perpanjanganWaktu' => fn ($q) => $q->orderByDesc('created_at'),
            'grupAnggota.pegawai:id,nama',
        ])->findOrFail($id);

        $this->authorize('view', $penugasan);

        $user = $request->user();
        $izin = [
            'terima' => Gate::forUser($user)->allows('terima', $penugasan),
            'tolak' => Gate::forUser($user)->allows('tolak', $penugasan),
            'submit' => Gate::forUser($user)->allows('submit', $penugasan),
            'update' => Gate::forUser($user)->allows('update', $penugasan),
            'delete' => Gate::forUser($user)->allows('delete', $penugasan),
            'uploadEviden' => Gate::forUser($user)->allows('uploadEviden', $penugasan),
            'nilai' => Gate::forUser($user)->allows('nilai', $penugasan),
            'revisi' => Gate::forUser($user)->allows('revisi', $penugasan),
            'approveMandiri' => Gate::forUser($user)->allows('approveMandiri', $penugasan),
            'rejectMandiri' => Gate::forUser($user)->allows('rejectMandiri', $penugasan),
            'ajukanPerpanjangan' => Gate::forUser($user)->allows('ajukanPerpanjangan', $penugasan),
            'putuskanPerpanjangan' => Gate::forUser($user)->allows('putuskanPerpanjangan', $penugasan)
                && $penugasan->perpanjanganWaktu->contains('status', PerpanjanganWaktu::STATUS_MENUNGGU),
        ];

        return view('penugasan::penugasan.detail', compact('penugasan', 'izin'));
    }

    /**
     * Endpoint ringan yang di-polling halaman detail tugas untuk mendeteksi ada tidaknya
     * perubahan (status, progress, bukti baru, revisi, pengajuan perpanjangan) sejak
     * halaman dimuat — supaya halaman detail terasa dinamis tanpa perlu partial-render
     * ulang seluruh tombol aksi/modal yang bergantung pada $izin per status/role.
     */
    public function meta(Request $request, string $id)
    {
        $penugasan = Penugasan::findOrFail($id);
        $this->authorize('view', $penugasan);

        // Dihitung lewat query relasi biasa (bukan withCount) — subquery withCount() pada relasi
        // polimorfik attachedFiles (morphMany, kolom UUID) gagal di Postgres ("operator does not
        // exist: uuid = character varying") karena binding tipe tidak otomatis ter-cast di subquery.
        return response()->json([
            'updated_at' => optional($penugasan->updated_at)->toIso8601String(),
            'status' => $penugasan->status,
            'progress_persen' => (float) $penugasan->progress_persen,
            'attached_files_count' => $penugasan->attachedFiles()->count(),
            'progress_count' => $penugasan->progress()->count(),
            'history_revisi_count' => $penugasan->historyRevisi()->count(),
            'perpanjangan_waktu_count' => $penugasan->perpanjanganWaktu()->count(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $validated = $request->validate(PenugasanActionService::aturanPerbarui());

            $penugasan = $this->actionService->perbarui($penugasan, $validated, $request->user());

            return response()->json(['success' => true, 'message' => 'Penugasan berhasil diperbarui', 'data' => $penugasan]);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $this->actionService->hapus($penugasan, $request->user());

            return response()->json(['success' => true, 'message' => 'Penugasan berhasil dihapus']);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        }
    }

    public function terima(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $penugasan = $this->actionService->terima($penugasan, $request->user());

            return response()->json(['success' => true, 'message' => 'Penugasan diterima dan mulai dikerjakan', 'data' => $penugasan]);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage() ?: 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
        }
    }

    public function tolak(Request $request, string $id)
    {
        try {
            $validated = $request->validate(['alasan_penolakan' => 'required|string|max:1000']);
            $penugasan = Penugasan::findOrFail($id);
            $this->actionService->tolak($penugasan, $request->user(), $validated['alasan_penolakan']);

            return response()->json(['success' => true, 'message' => 'Penugasan berhasil ditolak']);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage() ?: 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first(), 'errors' => $e->errors()], 422);
        }
    }

    public function submit(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $penugasan = $this->actionService->submit($penugasan, $request->user());

            return response()->json(['success' => true, 'message' => 'Penugasan berhasil diajukan Selesai', 'data' => $penugasan]);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage() ?: 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
        }
    }

    /**
     * Atasan mengisi realisasi tugas berstatus Selesai yang belum pernah dinilai.
     */
    public function nilai(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $validated = $request->validate(PenugasanActionService::aturanNilai());

            $penugasan = $this->actionService->nilai($penugasan, $validated, $request->user());

            return response()->json(['success' => true, 'message' => 'Penilaian berhasil disimpan', 'data' => $penugasan]);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first(), 'errors' => $e->errors()], 422);
        }
    }

    /**
     * Atasan memberi revisi pasca-Selesai, hanya sebelum dinilai (aturan E8).
     */
    public function revisi(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $validated = $request->validate([
                'catatan_revisi' => 'required|string',
                'deadline_baru' => 'required|date|after:today',
            ]);

            $penugasan = $this->actionService->revisi($penugasan, $validated, $request->user());

            return response()->json(['success' => true, 'message' => 'Revisi berhasil diberikan', 'data' => $penugasan]);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first(), 'errors' => $e->errors()], 422);
        }
    }

    public function approveMandiri(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $validated = $request->validate([
                'prioritas' => 'sometimes|in:'.implode(',', Penugasan::PRIORITASES),
                'bobot_persen' => 'sometimes|numeric|min:0|max:100',
                'tanggal_mulai' => 'sometimes|date',
                'deadline_terbaru' => 'sometimes|date',
            ]);

            $penugasan = $this->actionService->approveMandiri($penugasan, $validated, $request->user());

            return response()->json(['success' => true, 'message' => 'Tugas mandiri disetujui', 'data' => $penugasan]);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
        }
    }

    public function rejectMandiri(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $validated = $request->validate(['alasan_reject' => 'required|string|max:1000']);

            $penugasan = $this->actionService->rejectMandiri($penugasan, $validated, $request->user());

            return response()->json(['success' => true, 'message' => 'Tugas mandiri ditolak', 'data' => $penugasan]);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
        }
    }

    public function ajukanPerpanjangan(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $validated = $request->validate([
                'deadline_diminta' => 'required|date|after:'.($penugasan->deadline_terbaru?->toDateString() ?? $penugasan->tanggal_selesai->toDateString()),
                'alasan_pengajuan' => 'required|string',
            ]);

            $pengajuan = $this->actionService->ajukanPerpanjangan($penugasan, $validated, $request->user());

            return response()->json(['success' => true, 'message' => 'Pengajuan perpanjangan waktu berhasil dikirim', 'data' => $pengajuan]);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first(), 'errors' => $e->errors()], 422);
        }
    }

    public function setujuiPerpanjangan(Request $request, string $id, string $perpanjanganId)
    {
        return $this->putuskanPerpanjangan($request, $id, $perpanjanganId, disetujui: true);
    }

    public function tolakPerpanjangan(Request $request, string $id, string $perpanjanganId)
    {
        return $this->putuskanPerpanjangan($request, $id, $perpanjanganId, disetujui: false);
    }

    private function putuskanPerpanjangan(Request $request, string $id, string $perpanjanganId, bool $disetujui)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);

            $validated = $disetujui
                ? $request->validate(['deadline_disetujui' => 'required|date', 'catatan_atasan' => 'nullable|string'])
                : $request->validate(['catatan_atasan' => 'nullable|string']);

            $pengajuan = $this->actionService->putuskanPerpanjangan($penugasan, $perpanjanganId, $disetujui, $validated, $request->user());

            $message = $disetujui ? 'Perpanjangan waktu disetujui' : 'Perpanjangan waktu ditolak';

            return response()->json(['success' => true, 'message' => $message, 'data' => $pengajuan]);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first(), 'errors' => $e->errors()], 422);
        }
    }

    public function updateProgress(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'progress_persen' => 'required|numeric|min:0|max:100',
                'deskripsi_kegiatan' => 'required|string',
                'kendala' => 'nullable|string',
            ]);

            $penugasan = Penugasan::findOrFail($id);
            $penugasan = $this->actionService->updateProgress($penugasan, $validated, $request->user());

            return response()->json(['success' => true, 'message' => 'Progress tugas berhasil diperbarui', 'data' => $penugasan]);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
        }
    }

    public function formUploadBukti(string $id)
    {
        $tugas = Penugasan::with(['pegawai:id,nama'])->findOrFail($id);

        if ($tugas->pegawai_id !== request()->user()->id) {
            abort(403, 'Tidak memiliki akses');
        }

        if ($tugas->mode_grup === Penugasan::MODE_GRUP_KOLEKTIF && ! $tugas->is_koordinator) {
            abort(403, 'Hanya koordinator grup yang bisa upload bukti pengerjaan untuk tugas ini');
        }

        return view('penugasan::penugasan.upload-eviden', ['tugas' => $tugas, 'jenisTugas' => $tugas->jenis]);
    }

    /**
     * Upload bukti pengerjaan (eviden) dan lampirkan ke penugasan (polymorphic attachedFiles).
     */
    public function uploadBukti(Request $request, string $id)
    {
        $penugasan = Penugasan::findOrFail($id);

        if ($request->user()->id !== $penugasan->pegawai_id) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        }

        if ($penugasan->mode_grup === Penugasan::MODE_GRUP_KOLEKTIF && ! $penugasan->is_koordinator) {
            return response()->json(['success' => false, 'message' => 'Hanya koordinator grup yang bisa upload bukti pengerjaan untuk tugas ini'], 403);
        }

        $validated = $request->validate([
            'folder_id' => 'required|uuid|exists:td_folders,id',
            'file' => [
                'required',
                'file',
                'max:102400',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,bmp,svg,webp',
            ],
        ], [
            'file.mimes' => 'File harus berupa dokumen (PDF, Word, Excel, PowerPoint) atau gambar (JPG, PNG, GIF, dll)',
            'file.max' => 'Ukuran file maksimal 100MB',
        ]);

        try {
            $folder = TdFolder::findOrFail($validated['folder_id']);
            $this->authorize('upload', [TdFile::class, $folder]);

            $uploadedFile = $request->file('file');
            $originalName = $uploadedFile->getClientOriginalName();
            $extension = $uploadedFile->getClientOriginalExtension();
            $filename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)).'_'.time().'.'.$extension;
            $storagePath = "terminal-data/{$folder->bidang_id}/{$folder->id}";
            $path = $uploadedFile->storeAs($storagePath, $filename);
            $hash = hash_file('sha256', $uploadedFile->getRealPath());

            $atributFile = [
                'folder_id' => $folder->id,
                'bidang_id' => $folder->bidang_id,
                'sub_bidang_id' => $folder->sub_bidang_id,
                'name' => pathinfo($originalName, PATHINFO_FILENAME),
                'original_name' => $originalName,
                'storage_path' => $path,
                'extension' => $extension,
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
                'hash' => $hash,
                'version' => 1,
                'is_latest_version' => true,
                'created_by' => $request->user()->id,
            ];

            $file = $penugasan->attachedFiles()->create($atributFile);

            // Penilaian tim (mode_grup kolektif) dinilai satu kesatuan — bukti pengerjaan harus
            // ikut disinkronkan ke record penugasan setiap anggota grup, bukan hanya ke record
            // pengunggah, supaya semua anggota (dan atasan saat membuka detail siapa pun) melihat
            // bukti yang sama. TdFile hanya bisa menempel ke satu attachable, jadi bukan reuse baris,
            // melainkan baris TdFile baru per anggota yang menunjuk ke file fisik yang sama di disk.
            if ($penugasan->mode_grup === Penugasan::MODE_GRUP_KOLEKTIF) {
                foreach ($penugasan->grupAnggota as $anggota) {
                    $anggota->attachedFiles()->create($atributFile);
                }
            }

            $folder->updateStats();

            return response()->json([
                'success' => true,
                'message' => 'Bukti pengerjaan berhasil diupload',
                'data' => ['id' => $file->id, 'name' => $file->original_name],
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk upload file ke folder ini.',
            ], 403);
        }
    }

    /**
     * Hapus bukti pengerjaan (eviden). Otorisasi mengikuti aturan yang sama dengan upload
     * (pemilik tugas, status masih proses/revisi/terlambat) — bukan lewat TdFilePolicy generik,
     * karena itu tidak mengecek status penugasan.
     */
    public function hapusBukti(Request $request, string $id, string $fileId)
    {
        $penugasan = Penugasan::findOrFail($id);

        try {
            Gate::forUser($request->user())->authorize('uploadEviden', $penugasan);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini'], 403);
        }

        if ($penugasan->mode_grup === Penugasan::MODE_GRUP_KOLEKTIF && ! $penugasan->is_koordinator) {
            return response()->json(['success' => false, 'message' => 'Hanya koordinator grup yang bisa menghapus bukti pengerjaan untuk tugas ini'], 403);
        }

        $file = $penugasan->attachedFiles()->find($fileId);

        if (! $file) {
            return response()->json(['success' => false, 'message' => 'File tidak ditemukan'], 404);
        }

        // Mode kolektif: satu upload menghasilkan beberapa baris TdFile (satu per anggota grup) yang
        // menunjuk ke file fisik yang sama di disk (lihat uploadBukti()). Menghapus satu baris saja
        // akan menghapus file fisik dan membuat baris anggota lain jadi orphan — jadi semua baris
        // yang menunjuk storage_path yang sama ikut dihapus sekaligus. attachable_type disimpan
        // sebagai alias morph map ("penugasan"), BUKAN nama class penuh — pakai getMorphClass()
        // supaya query ini cocok dengan nilai yang sebenarnya tersimpan (lihat AppServiceProvider::boot()).
        TdFile::where('attachable_type', $penugasan->getMorphClass())
            ->where('storage_path', $file->storage_path)
            ->get()
            ->each->delete();

        return response()->json(['success' => true, 'message' => 'Bukti pengerjaan berhasil dihapus']);
    }

    /**
     * Daftar pegawai yang bisa ditugaskan oleh user login, mengikuti aturan yang
     * sama persis dengan PenugasanPolicy::assignTo() — hanya untuk mengisi opsi
     * di wizard, otorisasi final tetap divalidasi server-side lewat policy.
     *
     * @return Collection<int, User>
     */
    private function calonPegawaiBisaDitugaskan(User $user): Collection
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        $query = User::whereRelation('profile', 'status_aktif', 'Aktif')->where('id', '!=', $user->id);

        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'], true)) {
            $query->whereHas('profile.jabatan', fn ($q) => $q->where('kode', '!=', 'GATEK'));
        } elseif ($kodeJabatan === 'KABID') {
            $query->where(function ($q) use ($user) {
                $q->whereRelation('profile', 'bidang_id', $user->profile?->bidang_id)
                    ->orWhereHas('profile.jabatan', fn ($sub) => $sub->where('kode', 'GATEK'));
            });
        } elseif ($kodeJabatan === 'KASUBAG') {
            $query->where(function ($q) use ($user) {
                $q->whereRelation('profile', 'atasan_langsung_id', $user->id)
                    ->orWhereHas('profile.jabatan', fn ($sub) => $sub->where('kode', 'GATEK'));
            });
        } elseif ($kodeJabatan === 'JAFUNG') {
            $query->whereRelation('profile', 'bidang_id', $user->profile?->bidang_id)
                ->whereHas('profile.jabatan', fn ($sub) => $sub->whereIn('kode', ['PELAKSANA', 'GATEK']));
        } else {
            $query->whereRelation('profile', 'atasan_langsung_id', $user->id);
        }

        return $query->with(['profile.jabatan', 'profile.bidang'])->orderBy('nama')->get();
    }
}

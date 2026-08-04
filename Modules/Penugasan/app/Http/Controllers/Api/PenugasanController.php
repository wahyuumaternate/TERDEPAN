<?php

namespace Modules\Penugasan\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Services\AtasanMandiriEligibility;
use Modules\Penugasan\Services\PenugasanActionService;

/**
 * @OA\Tag(
 *     name="Penugasan",
 *     description="API Penugasan. Status: pending, proses, revisi, terlambat, selesai, ditolak. Nilai akhir = (bobot_persen x realisasi_persen / 100) x (1 - persentase_terlambat / 100)."
 * )
 *
 * @OA\Get(
 *     path="/penugasan",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="List penugasan (filter: jenis, status, pegawai_id)",
 *
 *     @OA\Parameter(name="jenis", in="query", @OA\Schema(type="string", enum={"pokok","tambahan"})),
 *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"pending","proses","revisi","terlambat","selesai","ditolak"})),
 *     @OA\Parameter(name="pegawai_id", in="query", @OA\Schema(type="integer")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Get(
 *     path="/penugasan/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Detail penugasan",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki akses"),
 *     @OA\Response(response=404, description="Penugasan tidak ditemukan")
 * )
 *
 * @OA\Post(
 *     path="/penugasan",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Buat penugasan baru. pegawai_id == user login berarti tugas mandiri (wajib sertakan atasan_id, menunggu approval atasan); selain itu pemberi_tugas_id otomatis diisi user login",
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"pegawai_id","jenis","prioritas","nama_tugas","deskripsi","tanggal_mulai","tanggal_selesai"},
 *
 *             @OA\Property(property="pegawai_id", type="integer", example=1),
 *             @OA\Property(property="atasan_id", type="integer", example=2, description="Wajib jika pegawai_id == user login (tugas mandiri)"),
 *             @OA\Property(property="jenis", type="string", enum={"pokok","tambahan"}, example="tambahan"),
 *             @OA\Property(property="prioritas", type="string", enum={"rendah","sedang","tinggi"}, example="sedang"),
 *             @OA\Property(property="nama_tugas", type="string", example="Menyusun laporan triwulan"),
 *             @OA\Property(property="deskripsi", type="string", example="Menyusun dan mengumpulkan laporan triwulan bidang"),
 *             @OA\Property(property="alasan_penugasan", type="string", example="Menggantikan staf yang cuti"),
 *             @OA\Property(property="tanggal_mulai", type="string", format="date", example="2026-08-01"),
 *             @OA\Property(property="tanggal_selesai", type="string", format="date", example="2026-08-15"),
 *             @OA\Property(property="target_value", type="number", example=1),
 *             @OA\Property(property="satuan", type="string", example="dokumen"),
 *             @OA\Property(property="bobot_persen", type="number", example=15, description="Opsional — boleh diisi di awal atau ditunda sampai POST .../nilai (aturan E4)")
 *         )
 *     ),
 *
 *     @OA\Response(response=201, description="Created"),
 *     @OA\Response(response=403, description="Tidak memiliki hak akses untuk memberikan tugas ke pegawai ini"),
 *     @OA\Response(response=422, description="Validasi gagal")
 * )
 *
 * @OA\Put(
 *     path="/penugasan/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Update penugasan (pemberi tugas saat status pending, atau pegawai sendiri untuk tugas mandiri berstatus ditolak)",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin"),
 *     @OA\Response(response=422, description="Validasi gagal")
 * )
 *
 * @OA\Delete(
 *     path="/penugasan/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Hapus penugasan (pemberi tugas saat status pending, atau pegawai sendiri untuk tugas mandiri berstatus ditolak)",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin")
 * )
 *
 * @OA\Get(
 *     path="/penugasan/atasan-mandiri",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Daftar kandidat atasan yang boleh dipilih user login saat mengajukan tugas mandiri",
 *
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Post(
 *     path="/penugasan/{id}/approve-mandiri",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Atasan terpilih menyetujui tugas mandiri (pending -> proses). prioritas opsional (override), bobot_persen/tanggal_mulai/deadline_terbaru opsional",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin"),
 *     @OA\Response(response=422, description="Status tidak sesuai")
 * )
 *
 * @OA\Post(
 *     path="/penugasan/{id}/reject-mandiri",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Atasan terpilih menolak tugas mandiri (pending -> ditolak), wajib alasan_reject",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin"),
 *     @OA\Response(response=422, description="Status tidak sesuai")
 * )
 *
 * @OA\Post(
 *     path="/penugasan/{id}/terima",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Pegawai menerima penugasan bukan-mandiri (pending -> proses)",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin"),
 *     @OA\Response(response=422, description="Status tidak sesuai")
 * )
 *
 * @OA\Post(
 *     path="/penugasan/{id}/tolak",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Pegawai menolak penugasan bukan-mandiri (status pending)",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(required={"alasan_penolakan"}, @OA\Property(property="alasan_penolakan", type="string"))
 *     ),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin"),
 *     @OA\Response(response=422, description="Status tidak sesuai")
 * )
 *
 * @OA\Post(
 *     path="/penugasan/{id}/submit",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Pegawai mengajukan Selesai (proses/revisi/terlambat -> selesai)",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin"),
 *     @OA\Response(response=422, description="Status tidak sesuai atau belum ada bukti pengerjaan")
 * )
 *
 * @OA\Post(
 *     path="/penugasan/{id}/nilai",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Atasan mengisi realisasi (selesai, belum dinilai). Menghitung nilai_awal, persentase_terlambat, nilai_akhir",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"realisasi_persen"},
 *
 *             @OA\Property(property="realisasi_persen", type="number", example=90),
 *             @OA\Property(property="bobot_persen", type="number", example=80, description="Wajib jika belum pernah diisi sebelumnya"),
 *             @OA\Property(property="catatan_validasi", type="string")
 *         )
 *     ),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin"),
 *     @OA\Response(response=422, description="Validasi gagal, status tidak sesuai, atau sudah dinilai")
 * )
 *
 * @OA\Post(
 *     path="/penugasan/{id}/revisi",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Atasan memberi revisi pasca-Selesai, hanya sebelum dinilai (selesai -> revisi, deadline_terbaru diperbarui)",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(required={"catatan_revisi","deadline_baru"}, @OA\Property(property="catatan_revisi", type="string"), @OA\Property(property="deadline_baru", type="string", format="date"))
 *     ),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin"),
 *     @OA\Response(response=422, description="Status tidak sesuai atau sudah dinilai")
 * )
 *
 * @OA\Post(
 *     path="/penugasan/{id}/perpanjangan-waktu",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Pegawai mengajukan perpanjangan waktu (maks. 3x disetujui), wajib alasan_pengajuan",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\Response(response=201, description="Created"),
 *     @OA\Response(response=403, description="Tidak memiliki izin"),
 *     @OA\Response(response=422, description="Status tidak sesuai atau kuota habis")
 * )
 *
 * @OA\Get(
 *     path="/penugasan/{id}/perpanjangan-waktu",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Riwayat pengajuan perpanjangan waktu tugas",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Post(
 *     path="/penugasan/{id}/perpanjangan-waktu/{perpanjanganId}/setujui",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Atasan menyetujui perpanjangan waktu, wajib deadline_disetujui (boleh beda dari yang diminta)",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Parameter(name="perpanjanganId", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin")
 * )
 *
 * @OA\Post(
 *     path="/penugasan/{id}/perpanjangan-waktu/{perpanjanganId}/tolak",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Atasan menolak perpanjangan waktu",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Parameter(name="perpanjanganId", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin")
 * )
 *
 * @OA\Post(
 *     path="/penugasan/berikan-tugas-grup",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Beri satu tugas ke lebih dari satu pegawai sekaligus (mode kolektif/per_orang)",
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"pegawai_ids","mode_grup","jenis","prioritas","nama_tugas","deskripsi","tanggal_mulai","tanggal_selesai"},
 *
 *             @OA\Property(property="pegawai_ids", type="array", @OA\Items(type="integer"), example={2,3,4}),
 *             @OA\Property(property="mode_grup", type="string", enum={"kolektif","per_orang"}),
 *             @OA\Property(property="koordinator_id", type="integer", description="Wajib jika mode_grup = kolektif")
 *         )
 *     ),
 *
 *     @OA\Response(response=201, description="Created"),
 *     @OA\Response(response=403, description="Tidak memiliki hak akses ke salah satu pegawai"),
 *     @OA\Response(response=422, description="Validasi gagal")
 * )
 *
 * @OA\Post(
 *     path="/penugasan/{id}/progress",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Catat progress harian dan update progress_persen berjalan",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"progress_persen","deskripsi_kegiatan"},
 *
 *             @OA\Property(property="progress_persen", type="number", example=50),
 *             @OA\Property(property="deskripsi_kegiatan", type="string", example="Mengumpulkan data dari bidang terkait"),
 *             @OA\Property(property="kendala", type="string", nullable=true)
 *         )
 *     ),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin"),
 *     @OA\Response(response=422, description="Validasi gagal")
 * )
 */
class PenugasanController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly PenugasanActionService $actionService) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Penugasan::with(['pegawai:id,nama', 'pemberiTugas:id,nama', 'validator:id,nama']);

        // Default: hanya penugasan milik sendiri atau yang diberikan sendiri,
        // kecuali eksplisit filter pegawai_id oleh atasan yang berhak.
        if ($request->filled('pegawai_id')) {
            $query->where('pegawai_id', $request->integer('pegawai_id'));
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('pegawai_id', $user->id)->orWhere('pemberi_tugas_id', $user->id);
            });
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->string('jenis'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $data = $query->orderByDesc('tanggal_mulai')->paginate($request->integer('per_page', 20));

        return response()->json([
            'status' => true,
            'message' => 'List penugasan',
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate(PenugasanActionService::aturanBuat());

            $penugasan = $this->actionService->buat($validated, $request->user());

            return response()->json([
                'status' => true,
                'message' => 'Penugasan berhasil dibuat',
                'data' => $penugasan,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'data' => $e->errors(),
            ], 422);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage() ?: 'Tidak memiliki hak akses untuk memberikan tugas ke pegawai ini',
                'data' => null,
            ], 403);
        }
    }

    /**
     * Beri satu tugas ke lebih dari satu pegawai sekaligus (aturan bisnis §1.D).
     */
    public function berikanTugasGrup(Request $request)
    {
        try {
            $validated = $request->validate(PenugasanActionService::aturanBuatGrup());

            $rows = $this->actionService->buatGrup($validated, $request->user());

            return response()->json([
                'status' => true,
                'message' => 'Penugasan grup berhasil dibuat',
                'data' => $rows,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validasi gagal', 'data' => $e->errors()], 422);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => 'Tidak memiliki hak akses untuk memberikan tugas ke salah satu pegawai', 'data' => null], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Salah satu pegawai tidak ditemukan', 'data' => null], 404);
        }
    }

    /**
     * Daftar kandidat atasan yang boleh dipilih user login untuk tugas mandiri.
     */
    public function atasanMandiri(Request $request)
    {
        $kandidat = app(AtasanMandiriEligibility::class)->kandidatUntuk($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Daftar kandidat atasan',
            'data' => $kandidat->values(['id', 'nama']),
        ]);
    }

    public function show(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::with([
                'pegawai:id,nama', 'pemberiTugas:id,nama', 'validator:id,nama',
                'attachedFiles', 'progress' => fn ($q) => $q->orderByDesc('tanggal'),
                'historyRevisi' => fn ($q) => $q->orderByDesc('revisi_ke'),
                'perpanjanganWaktu' => fn ($q) => $q->orderByDesc('created_at'),
            ])->findOrFail($id);

            $this->authorize('view', $penugasan);

            return response()->json([
                'status' => true,
                'message' => 'Detail penugasan',
                'data' => $penugasan,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Penugasan tidak ditemukan',
                'data' => null,
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak memiliki akses ke penugasan ini',
                'data' => null,
            ], 403);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $validated = $request->validate(PenugasanActionService::aturanPerbarui());

            $penugasan = $this->actionService->perbarui($penugasan, $validated, $request->user());

            return response()->json([
                'status' => true,
                'message' => 'Penugasan berhasil diperbarui',
                'data' => $penugasan,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Penugasan tidak ditemukan', 'data' => null], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini', 'data' => null], 403);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validasi gagal', 'data' => $e->errors()], 422);
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $this->actionService->hapus($penugasan, $request->user());

            return response()->json(['status' => true, 'message' => 'Penugasan berhasil dihapus', 'data' => null]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Penugasan tidak ditemukan', 'data' => null], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini', 'data' => null], 403);
        }
    }

    public function terima(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $penugasan = $this->actionService->terima($penugasan, $request->user());

            return response()->json(['status' => true, 'message' => 'Penugasan diterima dan mulai dikerjakan', 'data' => $penugasan]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Penugasan tidak ditemukan', 'data' => null], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage() ?: 'Tidak memiliki izin untuk melakukan aksi ini', 'data' => null], 403);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => collect($e->errors())->flatten()->first(), 'data' => null], 422);
        }
    }

    public function tolak(Request $request, string $id)
    {
        try {
            $validated = $request->validate(['alasan_penolakan' => 'required|string|max:1000']);
            $penugasan = Penugasan::findOrFail($id);
            $this->actionService->tolak($penugasan, $request->user(), $validated['alasan_penolakan']);

            return response()->json(['status' => true, 'message' => 'Penugasan berhasil ditolak', 'data' => null]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Penugasan tidak ditemukan', 'data' => null], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage() ?: 'Tidak memiliki izin untuk melakukan aksi ini', 'data' => null], 403);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => collect($e->errors())->flatten()->first(), 'data' => $e->errors()], 422);
        }
    }

    public function submit(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $penugasan = $this->actionService->submit($penugasan, $request->user());

            return response()->json(['status' => true, 'message' => 'Penugasan berhasil diajukan Selesai', 'data' => $penugasan]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Penugasan tidak ditemukan', 'data' => null], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage() ?: 'Tidak memiliki izin untuk melakukan aksi ini', 'data' => null], 403);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => collect($e->errors())->flatten()->first(), 'data' => null], 422);
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

            return response()->json(['status' => true, 'message' => 'Penilaian berhasil disimpan', 'data' => $penugasan]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Penugasan tidak ditemukan', 'data' => null], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini', 'data' => null], 403);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validasi gagal', 'data' => $e->errors()], 422);
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

            return response()->json(['status' => true, 'message' => 'Revisi berhasil diberikan', 'data' => $penugasan]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Penugasan tidak ditemukan', 'data' => null], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini', 'data' => null], 403);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validasi gagal', 'data' => $e->errors()], 422);
        }
    }

    /**
     * Atasan terpilih menyetujui tugas mandiri (pending -> proses).
     */
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

            return response()->json(['status' => true, 'message' => 'Tugas mandiri disetujui', 'data' => $penugasan]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Penugasan tidak ditemukan', 'data' => null], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini', 'data' => null], 403);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validasi gagal', 'data' => $e->errors()], 422);
        }
    }

    /**
     * Atasan terpilih menolak tugas mandiri (pending -> ditolak).
     */
    public function rejectMandiri(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $validated = $request->validate([
                'alasan_reject' => 'required|string|max:1000',
            ]);

            $penugasan = $this->actionService->rejectMandiri($penugasan, $validated, $request->user());

            return response()->json(['status' => true, 'message' => 'Tugas mandiri ditolak', 'data' => $penugasan]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Penugasan tidak ditemukan', 'data' => null], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini', 'data' => null], 403);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validasi gagal', 'data' => $e->errors()], 422);
        }
    }

    /**
     * Pegawai mengajukan perpanjangan waktu (aturan E7), maks. 3x disetujui.
     */
    public function ajukanPerpanjangan(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $validated = $request->validate([
                'deadline_diminta' => 'required|date|after:'.($penugasan->deadline_terbaru?->toDateString() ?? $penugasan->tanggal_selesai->toDateString()),
                'alasan_pengajuan' => 'required|string',
            ]);

            $pengajuan = $this->actionService->ajukanPerpanjangan($penugasan, $validated, $request->user());

            return response()->json(['status' => true, 'message' => 'Pengajuan perpanjangan waktu berhasil dikirim', 'data' => $pengajuan], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Penugasan tidak ditemukan', 'data' => null], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini', 'data' => null], 403);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validasi gagal', 'data' => $e->errors()], 422);
        }
    }

    public function riwayatPerpanjangan(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $this->authorize('view', $penugasan);

            $riwayat = $penugasan->perpanjanganWaktu()->orderByDesc('created_at')->get();

            return response()->json(['status' => true, 'message' => 'Riwayat perpanjangan waktu', 'data' => $riwayat]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Penugasan tidak ditemukan', 'data' => null], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => 'Tidak memiliki akses ke penugasan ini', 'data' => null], 403);
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

            return response()->json(['status' => true, 'message' => $message, 'data' => $pengajuan]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Penugasan atau pengajuan tidak ditemukan', 'data' => null], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini', 'data' => null], 403);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validasi gagal', 'data' => $e->errors()], 422);
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

            return response()->json(['status' => true, 'message' => 'Progress berhasil dicatat', 'data' => $penugasan]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Penugasan tidak ditemukan', 'data' => null], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini', 'data' => null], 403);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validasi gagal', 'data' => $e->errors()], 422);
        }
    }
}

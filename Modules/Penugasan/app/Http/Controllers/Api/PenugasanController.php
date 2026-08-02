<?php

namespace Modules\Penugasan\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Penugasan\Models\HistoriRevisi;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Models\PerpanjanganWaktu;
use Modules\Penugasan\Models\Progress;
use Modules\Penugasan\Services\AtasanMandiriEligibility;
use Modules\Penugasan\Services\HitungKeterlambatan;

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
            $validated = $request->validate([
                'pegawai_id' => 'required|exists:users,id',
                'atasan_id' => 'nullable|exists:users,id',
                'jenis' => 'required|in:'.implode(',', Penugasan::JENISES),
                'prioritas' => 'required|in:'.implode(',', Penugasan::PRIORITASES),
                'nama_tugas' => 'required|string|max:255',
                'deskripsi' => 'required|string',
                'alasan_penugasan' => 'nullable|string',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'target_value' => 'nullable|numeric|min:0',
                'satuan' => 'nullable|string|max:30',
                'bobot_persen' => 'nullable|numeric|min:0|max:100',
            ]);

            $user = $request->user();
            $isSelfInitiated = ((int) $validated['pegawai_id']) === $user->id;

            $pemberiTugasId = $user->id;

            if ($isSelfInitiated) {
                if (empty($validated['atasan_id'])) {
                    throw ValidationException::withMessages([
                        'atasan_id' => 'Pilih salah satu atasan yang berhak menyetujui tugas mandiri ini.',
                    ]);
                }

                $atasan = User::findOrFail($validated['atasan_id']);

                if (! app(AtasanMandiriEligibility::class)->bolehDipilih($user, $atasan)) {
                    throw ValidationException::withMessages([
                        'atasan_id' => 'Atasan yang dipilih tidak berhak menyetujui tugas mandiri Anda.',
                    ]);
                }

                $pemberiTugasId = $atasan->id;
            } else {
                $this->authorize('create', Penugasan::class);
                $target = User::findOrFail($validated['pegawai_id']);
                $this->authorize('assignTo', [Penugasan::class, $target]);
            }

            $penugasan = Penugasan::create([
                ...$validated,
                'pemberi_tugas_id' => $pemberiTugasId,
                'is_mandiri' => $isSelfInitiated,
                'deadline_terbaru' => $validated['tanggal_selesai'],
                'status' => Penugasan::STATUS_PENDING,
                'status_approval' => $isSelfInitiated ? Penugasan::APPROVAL_PENDING : null,
            ]);

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
            $validated = $request->validate([
                'pegawai_ids' => 'required|array|min:2',
                'pegawai_ids.*' => 'required|exists:users,id|distinct',
                'mode_grup' => 'required|in:'.implode(',', Penugasan::MODE_GRUPS),
                'koordinator_id' => 'required_if:mode_grup,'.Penugasan::MODE_GRUP_KOLEKTIF.'|nullable|integer',
                'jenis' => 'required|in:'.implode(',', Penugasan::JENISES),
                'prioritas' => 'required|in:'.implode(',', Penugasan::PRIORITASES),
                'nama_tugas' => 'required|string|max:255',
                'deskripsi' => 'required|string',
                'alasan_penugasan' => 'nullable|string',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'target_value' => 'nullable|numeric|min:0',
                'satuan' => 'nullable|string|max:30',
                'bobot_persen' => 'nullable|numeric|min:0|max:100',
            ]);

            if ($validated['mode_grup'] === Penugasan::MODE_GRUP_KOLEKTIF
                && ! in_array((int) $validated['koordinator_id'], array_map('intval', $validated['pegawai_ids']), true)) {
                throw ValidationException::withMessages([
                    'koordinator_id' => 'Koordinator harus salah satu dari pegawai_ids.',
                ]);
            }

            $user = $request->user();
            $this->authorize('create', Penugasan::class);

            foreach ($validated['pegawai_ids'] as $pegawaiId) {
                $this->authorize('assignTo', [Penugasan::class, User::findOrFail($pegawaiId)]);
            }

            $grupId = (string) Str::uuid();

            $rows = collect($validated['pegawai_ids'])->map(function ($pegawaiId) use ($validated, $user, $grupId) {
                return Penugasan::create([
                    'pegawai_id' => $pegawaiId,
                    'pemberi_tugas_id' => $user->id,
                    'is_mandiri' => false,
                    'grup_id' => $grupId,
                    'mode_grup' => $validated['mode_grup'],
                    'is_koordinator' => $validated['mode_grup'] === Penugasan::MODE_GRUP_KOLEKTIF
                        && (int) $pegawaiId === (int) $validated['koordinator_id'],
                    'jenis' => $validated['jenis'],
                    'prioritas' => $validated['prioritas'],
                    'nama_tugas' => $validated['nama_tugas'],
                    'deskripsi' => $validated['deskripsi'],
                    'alasan_penugasan' => $validated['alasan_penugasan'] ?? null,
                    'tanggal_mulai' => $validated['tanggal_mulai'],
                    'tanggal_selesai' => $validated['tanggal_selesai'],
                    'deadline_terbaru' => $validated['tanggal_selesai'],
                    'target_value' => $validated['target_value'] ?? null,
                    'satuan' => $validated['satuan'] ?? null,
                    'bobot_persen' => $validated['bobot_persen'] ?? null,
                    'status' => Penugasan::STATUS_PENDING,
                ]);
            });

            return response()->json([
                'status' => true,
                'message' => 'Penugasan grup berhasil dibuat',
                'data' => $rows->values(),
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
            $this->authorize('update', $penugasan);

            $validated = $request->validate([
                'nama_tugas' => 'sometimes|string|max:255',
                'deskripsi' => 'sometimes|string',
                'alasan_penugasan' => 'nullable|string',
                'tanggal_mulai' => 'sometimes|date',
                'tanggal_selesai' => 'sometimes|date|after_or_equal:tanggal_mulai',
                'target_value' => 'nullable|numeric|min:0',
                'satuan' => 'nullable|string|max:30',
                'bobot_persen' => 'sometimes|numeric|min:0|max:100',
                'prioritas' => 'sometimes|in:'.implode(',', Penugasan::PRIORITASES),
            ]);

            // Tugas mandiri yang Ditolak: edit berarti diajukan ulang (aturan E3)
            if ($penugasan->is_mandiri && $penugasan->status === Penugasan::STATUS_DITOLAK) {
                $validated['status'] = Penugasan::STATUS_PENDING;
                $validated['status_approval'] = Penugasan::APPROVAL_PENDING;
                $validated['alasan_reject'] = null;
            }

            if (isset($validated['tanggal_selesai'])) {
                $validated['deadline_terbaru'] = $validated['tanggal_selesai'];
            }

            $penugasan->update($validated);

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
            $this->authorize('delete', $penugasan);
            $penugasan->delete();

            return response()->json(['status' => true, 'message' => 'Penugasan berhasil dihapus', 'data' => null]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Penugasan tidak ditemukan', 'data' => null], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini', 'data' => null], 403);
        }
    }

    public function terima(Request $request, string $id)
    {
        $penugasan = Penugasan::findOrFail($id);

        if (! $this->userCanAuthorize('terima', $penugasan)) {
            return $this->forbidden();
        }

        if ($this->grupKolektifBukanKoordinator($penugasan)) {
            return $this->forbidden('Hanya koordinator grup yang bisa menerima tugas ini');
        }

        if ($penugasan->status !== Penugasan::STATUS_PENDING) {
            return $this->unprocessable('Penugasan hanya bisa diterima jika berstatus pending');
        }

        $penugasan->update(['status' => Penugasan::STATUS_PROSES, 'diterima_at' => now()]);
        $this->cascadeKeGrup($penugasan, ['status' => Penugasan::STATUS_PROSES, 'diterima_at' => now()]);

        return response()->json(['status' => true, 'message' => 'Penugasan diterima dan mulai dikerjakan', 'data' => $penugasan]);
    }

    public function tolak(Request $request, string $id)
    {
        $validated = $request->validate(['alasan_penolakan' => 'required|string|max:1000']);
        $penugasan = Penugasan::findOrFail($id);

        if (! $this->userCanAuthorize('tolak', $penugasan)) {
            return $this->forbidden();
        }

        if ($this->grupKolektifBukanKoordinator($penugasan)) {
            return $this->forbidden('Hanya koordinator grup yang bisa menolak tugas ini');
        }

        if ($penugasan->status !== Penugasan::STATUS_PENDING) {
            return $this->unprocessable('Hanya penugasan berstatus pending yang bisa ditolak');
        }

        $penugasan->update(['alasan_reject' => $validated['alasan_penolakan']]);

        if ($penugasan->mode_grup === Penugasan::MODE_GRUP_KOLEKTIF) {
            $penugasan->grupAnggota->each->delete();
        }

        $penugasan->delete();

        return response()->json(['status' => true, 'message' => 'Penugasan berhasil ditolak', 'data' => null]);
    }

    public function submit(Request $request, string $id)
    {
        $penugasan = Penugasan::findOrFail($id);

        if (! $this->userCanAuthorize('submit', $penugasan)) {
            return $this->forbidden();
        }

        if ($this->grupKolektifBukanKoordinator($penugasan)) {
            return $this->forbidden('Hanya koordinator grup yang bisa mengajukan Selesai untuk tugas ini');
        }

        if (! in_array($penugasan->status, [Penugasan::STATUS_PROSES, Penugasan::STATUS_REVISI, Penugasan::STATUS_TERLAMBAT])) {
            return $this->unprocessable('Penugasan hanya bisa diajukan Selesai dari status proses, revisi, atau terlambat');
        }

        if ($penugasan->attachedFiles()->count() === 0) {
            return $this->unprocessable('Harap upload bukti pengerjaan terlebih dahulu');
        }

        $selesaiAt = now();
        $penugasan->update(['status' => Penugasan::STATUS_SELESAI, 'tanggal_diselesaikan' => $selesaiAt]);
        $this->cascadeKeGrup($penugasan, ['status' => Penugasan::STATUS_SELESAI, 'tanggal_diselesaikan' => $selesaiAt]);

        return response()->json(['status' => true, 'message' => 'Penugasan berhasil diajukan Selesai', 'data' => $penugasan]);
    }

    /**
     * Atasan mengisi realisasi tugas berstatus Selesai yang belum pernah dinilai.
     */
    public function nilai(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $this->authorize('nilai', $penugasan);

            if ($penugasan->bobot_persen === null && ! $request->filled('bobot_persen')) {
                throw ValidationException::withMessages([
                    'bobot_persen' => 'Bobot belum pernah diisi, wajib disertakan saat menilai.',
                ]);
            }

            $validated = $request->validate([
                'realisasi_persen' => 'required|numeric|min:0|max:100',
                'bobot_persen' => 'sometimes|numeric|min:0|max:100',
                'catatan_validasi' => 'nullable|string',
            ]);

            $bobotPersen = (float) ($validated['bobot_persen'] ?? $penugasan->bobot_persen);
            $realisasiPersen = (float) $validated['realisasi_persen'];

            $deadline = $penugasan->deadline_terbaru ?? $penugasan->tanggal_selesai;
            $tanggalDiselesaikan = $penugasan->tanggal_diselesaikan ?? now();
            $persentaseTerlambat = app(HitungKeterlambatan::class)->persentase($deadline, $tanggalDiselesaikan);

            $nilaiAwal = round(($bobotPersen * $realisasiPersen) / 100, 2);
            $nilaiAkhir = $penugasan->hitungNilaiAkhir($nilaiAwal, $persentaseTerlambat);

            $update = [
                'bobot_persen' => $bobotPersen,
                'realisasi_persen' => $realisasiPersen,
                'nilai_awal' => $nilaiAwal,
                'persentase_terlambat' => $persentaseTerlambat,
                'nilai_akhir' => $nilaiAkhir,
                'hasil_validasi' => Penugasan::VALIDASI_DITERIMA,
                'catatan_validasi' => $validated['catatan_validasi'] ?? null,
                'validator_id' => $request->user()->id,
                'validated_at' => now(),
            ];

            $penugasan->update($update);
            $this->cascadeKeGrup($penugasan, $update);

            return response()->json(['status' => true, 'message' => 'Penilaian berhasil disimpan', 'data' => $penugasan->fresh()]);
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
            $this->authorize('revisi', $penugasan);

            $validated = $request->validate([
                'catatan_revisi' => 'required|string',
                'deadline_baru' => 'required|date|after:today',
            ]);

            $revisiKe = HistoriRevisi::where('penugasan_id', $penugasan->id)->max('revisi_ke');
            HistoriRevisi::create([
                'penugasan_id' => $penugasan->id,
                'revisi_ke' => ($revisiKe ?? 0) + 1,
                'tanggal_revisi' => now(),
                'catatan_revisi' => $validated['catatan_revisi'],
                'deadline_revisi' => $validated['deadline_baru'],
                'direvisi_oleh' => $request->user()->id,
                'pegawai_id' => $penugasan->pegawai_id,
            ]);

            $update = [
                'status' => Penugasan::STATUS_REVISI,
                'deadline_terbaru' => $validated['deadline_baru'],
                'tanggal_diselesaikan' => null,
            ];

            $penugasan->update($update);
            $this->cascadeKeGrup($penugasan, $update);

            return response()->json(['status' => true, 'message' => 'Revisi berhasil diberikan', 'data' => $penugasan->fresh()]);
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
            $this->authorize('approveMandiri', $penugasan);

            if ($penugasan->status !== Penugasan::STATUS_PENDING) {
                return $this->unprocessable('Tugas mandiri ini sudah diproses sebelumnya');
            }

            $validated = $request->validate([
                'prioritas' => 'sometimes|in:'.implode(',', Penugasan::PRIORITASES),
                'bobot_persen' => 'sometimes|numeric|min:0|max:100',
                'tanggal_mulai' => 'sometimes|date',
                'deadline_terbaru' => 'sometimes|date',
            ]);

            $penugasan->update([
                ...$validated,
                'status' => Penugasan::STATUS_PROSES,
                'status_approval' => Penugasan::APPROVAL_DITERIMA,
                'diterima_at' => now(),
            ]);

            return response()->json(['status' => true, 'message' => 'Tugas mandiri disetujui', 'data' => $penugasan->fresh()]);
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
            $this->authorize('rejectMandiri', $penugasan);

            if ($penugasan->status !== Penugasan::STATUS_PENDING) {
                return $this->unprocessable('Tugas mandiri ini sudah diproses sebelumnya');
            }

            $validated = $request->validate([
                'alasan_reject' => 'required|string|max:1000',
            ]);

            $penugasan->update([
                'status' => Penugasan::STATUS_DITOLAK,
                'status_approval' => Penugasan::APPROVAL_DITOLAK,
                'alasan_reject' => $validated['alasan_reject'],
            ]);

            return response()->json(['status' => true, 'message' => 'Tugas mandiri ditolak', 'data' => $penugasan->fresh()]);
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
            $this->authorize('ajukanPerpanjangan', $penugasan);

            $validated = $request->validate([
                'deadline_diminta' => 'required|date|after:'.($penugasan->deadline_terbaru?->toDateString() ?? $penugasan->tanggal_selesai->toDateString()),
                'alasan_pengajuan' => 'required|string',
            ]);

            $jumlahDisetujui = $penugasan->perpanjanganWaktu()->where('status', PerpanjanganWaktu::STATUS_DISETUJUI)->count();

            if ($jumlahDisetujui >= 3) {
                return $this->unprocessable('Batas perpanjangan waktu (3x) sudah tercapai');
            }

            $pengajuan = PerpanjanganWaktu::create([
                'penugasan_id' => $penugasan->id,
                'pegawai_id' => $penugasan->pegawai_id,
                'deadline_lama' => $penugasan->deadline_terbaru ?? $penugasan->tanggal_selesai,
                'deadline_diminta' => $validated['deadline_diminta'],
                'alasan_pengajuan' => $validated['alasan_pengajuan'],
                'status' => PerpanjanganWaktu::STATUS_MENUNGGU,
                'ke_berapa' => $jumlahDisetujui + 1,
            ]);

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
            $this->authorize('putuskanPerpanjangan', $penugasan);

            $pengajuan = $penugasan->perpanjanganWaktu()->where('id', $perpanjanganId)->firstOrFail();

            if ($pengajuan->status !== PerpanjanganWaktu::STATUS_MENUNGGU) {
                return $this->unprocessable('Pengajuan perpanjangan ini sudah diputuskan sebelumnya');
            }

            if ($disetujui) {
                $validated = $request->validate([
                    'deadline_disetujui' => 'required|date',
                    'catatan_atasan' => 'nullable|string',
                ]);

                $pengajuan->update([
                    'status' => PerpanjanganWaktu::STATUS_DISETUJUI,
                    'deadline_disetujui' => $validated['deadline_disetujui'],
                    'catatan_atasan' => $validated['catatan_atasan'] ?? null,
                    'disetujui_oleh_id' => $request->user()->id,
                ]);

                $update = ['deadline_terbaru' => $validated['deadline_disetujui']];
                $penugasan->update($update);
                $this->cascadeKeGrup($penugasan, $update);

                $message = 'Perpanjangan waktu disetujui';
            } else {
                $validated = $request->validate(['catatan_atasan' => 'nullable|string']);

                $pengajuan->update([
                    'status' => PerpanjanganWaktu::STATUS_DITOLAK,
                    'catatan_atasan' => $validated['catatan_atasan'] ?? null,
                    'disetujui_oleh_id' => $request->user()->id,
                ]);

                $message = 'Perpanjangan waktu ditolak';
            }

            return response()->json(['status' => true, 'message' => $message, 'data' => $pengajuan->fresh()]);
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

            if ($request->user()->id !== $penugasan->pegawai_id) {
                return $this->forbidden();
            }

            Progress::create([
                'penugasan_id' => $penugasan->id,
                'pegawai_id' => $penugasan->pegawai_id,
                'tanggal' => now()->toDateString(),
                'progress_persen' => $validated['progress_persen'],
                'deskripsi_kegiatan' => $validated['deskripsi_kegiatan'],
                'kendala' => $validated['kendala'] ?? null,
            ]);

            $penugasan->update(['progress_persen' => $validated['progress_persen']]);

            return response()->json(['status' => true, 'message' => 'Progress berhasil dicatat', 'data' => $penugasan->fresh()]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Penugasan tidak ditemukan', 'data' => null], 404);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validasi gagal', 'data' => $e->errors()], 422);
        }
    }

    private function userCanAuthorize(string $ability, Penugasan $penugasan): bool
    {
        return \Illuminate\Support\Facades\Gate::forUser(request()->user())->allows($ability, $penugasan);
    }

    /**
     * Mode Kolektif: hanya koordinator yang boleh bertindak atas nama grup (§9.1 poin 4).
     */
    private function grupKolektifBukanKoordinator(Penugasan $penugasan): bool
    {
        return $penugasan->mode_grup === Penugasan::MODE_GRUP_KOLEKTIF && ! $penugasan->is_koordinator;
    }

    /**
     * Cascade perubahan status/nilai/deadline ke seluruh anggota grup pada mode Kolektif.
     */
    private function cascadeKeGrup(Penugasan $penugasan, array $attrs): void
    {
        if ($penugasan->mode_grup === Penugasan::MODE_GRUP_KOLEKTIF) {
            $penugasan->grupAnggota()->update($attrs);
        }
    }

    private function forbidden(string $message = 'Tidak memiliki izin untuk melakukan aksi ini')
    {
        return response()->json(['status' => false, 'message' => $message, 'data' => null], 403);
    }

    private function unprocessable(string $message)
    {
        return response()->json(['status' => false, 'message' => $message, 'data' => null], 422);
    }
}

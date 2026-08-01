<?php

namespace Modules\Penugasan\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Penugasan\Models\HistoriRevisi;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Models\Progress;

/**
 * @OA\Tag(
 *     name="Penugasan",
 *     description="API Penugasan (gabungan Tugas Pokok + Tugas Tambahan + Tugas Harian). Nilai akhir dihitung dengan rumus bobot_persen x realisasi_persen / 100."
 * )
 *
 * @OA\Get(
 *     path="/penugasan",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="List penugasan (filter: jenis, status, pegawai_id)",
 *
 *     @OA\Parameter(name="jenis", in="query", @OA\Schema(type="string", enum={"pokok","tambahan"})),
 *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"pending","dikerjakan","validasi","revisi","selesai"})),
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
 *     summary="Buat penugasan baru. pegawai_id == user login berarti tugas mandiri (menunggu approval atasan); selain itu pemberi_tugas_id otomatis diisi user login",
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"pegawai_id","jenis","nama_tugas","deskripsi","tanggal_mulai","tanggal_selesai","bobot_persen"},
 *
 *             @OA\Property(property="pegawai_id", type="integer", example=1),
 *             @OA\Property(property="jenis", type="string", enum={"pokok","tambahan"}, example="tambahan"),
 *             @OA\Property(property="nama_tugas", type="string", example="Menyusun laporan triwulan"),
 *             @OA\Property(property="deskripsi", type="string", example="Menyusun dan mengumpulkan laporan triwulan bidang"),
 *             @OA\Property(property="alasan_penugasan", type="string", example="Menggantikan staf yang cuti"),
 *             @OA\Property(property="tanggal_mulai", type="string", format="date", example="2026-08-01"),
 *             @OA\Property(property="tanggal_selesai", type="string", format="date", example="2026-08-15"),
 *             @OA\Property(property="target_value", type="number", example=1),
 *             @OA\Property(property="satuan", type="string", example="dokumen"),
 *             @OA\Property(property="bobot_persen", type="number", example=15)
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
 *     summary="Update penugasan (hanya pemberi tugas, status masih pending)",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="nama_tugas", type="string"),
 *             @OA\Property(property="deskripsi", type="string"),
 *             @OA\Property(property="tanggal_mulai", type="string", format="date"),
 *             @OA\Property(property="tanggal_selesai", type="string", format="date"),
 *             @OA\Property(property="target_value", type="number"),
 *             @OA\Property(property="satuan", type="string"),
 *             @OA\Property(property="bobot_persen", type="number")
 *         )
 *     ),
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
 *     summary="Hapus penugasan (hanya pemberi tugas, status masih pending)",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin")
 * )
 *
 * @OA\Post(
 *     path="/penugasan/{id}/terima",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Pegawai menerima penugasan (pending -> dikerjakan)",
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
 *     summary="Pegawai menolak penugasan (status pending)",
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
 *     summary="Pegawai submit tugas untuk divalidasi (dikerjakan/revisi -> validasi)",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin"),
 *     @OA\Response(response=422, description="Status tidak sesuai atau belum ada bukti pengerjaan")
 * )
 *
 * @OA\Post(
 *     path="/penugasan/{id}/validasi",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Atasan memvalidasi tugas: menetapkan realisasi_persen, menghitung nilai_akhir = bobot_persen x realisasi_persen / 100",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"hasil_validasi"},
 *
 *             @OA\Property(property="hasil_validasi", type="string", enum={"diterima","revisi","ditolak"}),
 *             @OA\Property(property="realisasi_persen", type="number", example=90, description="Wajib jika hasil_validasi=diterima"),
 *             @OA\Property(property="catatan_validasi", type="string")
 *         )
 *     ),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin"),
 *     @OA\Response(response=422, description="Validasi gagal atau status tidak sesuai")
 * )
 *
 * @OA\Post(
 *     path="/penugasan/{id}/progress",
 *     security={{"bearerAuth":{}}},
 *     tags={"Penugasan"},
 *     summary="Catat progress harian (pengganti Tugas Harian lama) dan update progress_persen berjalan",
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

    public function show(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::with([
                'pegawai:id,nama', 'pemberiTugas:id,nama', 'validator:id,nama',
                'attachedFiles', 'progress' => fn ($q) => $q->orderByDesc('tanggal'),
                'historyRevisi' => fn ($q) => $q->orderByDesc('revisi_ke'),
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
            ]);

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

        if ($penugasan->status !== Penugasan::STATUS_PENDING) {
            return $this->unprocessable('Penugasan hanya bisa diterima jika berstatus pending');
        }

        $penugasan->update(['status' => Penugasan::STATUS_DIKERJAKAN]);

        return response()->json(['status' => true, 'message' => 'Penugasan diterima dan mulai dikerjakan', 'data' => $penugasan]);
    }

    public function tolak(Request $request, string $id)
    {
        $validated = $request->validate(['alasan_penolakan' => 'required|string|max:1000']);
        $penugasan = Penugasan::findOrFail($id);

        if (! $this->userCanAuthorize('tolak', $penugasan)) {
            return $this->forbidden();
        }

        if ($penugasan->status !== Penugasan::STATUS_PENDING) {
            return $this->unprocessable('Hanya penugasan berstatus pending yang bisa ditolak');
        }

        $penugasan->update(['alasan_reject' => $validated['alasan_penolakan']]);
        $penugasan->delete();

        return response()->json(['status' => true, 'message' => 'Penugasan berhasil ditolak', 'data' => null]);
    }

    public function submit(Request $request, string $id)
    {
        $penugasan = Penugasan::findOrFail($id);

        if (! $this->userCanAuthorize('submit', $penugasan)) {
            return $this->forbidden();
        }

        if (! in_array($penugasan->status, [Penugasan::STATUS_DIKERJAKAN, Penugasan::STATUS_REVISI])) {
            return $this->unprocessable('Penugasan hanya bisa disubmit jika sedang dikerjakan atau revisi');
        }

        if ($penugasan->attachedFiles()->count() === 0) {
            return $this->unprocessable('Harap upload bukti pengerjaan terlebih dahulu');
        }

        $penugasan->update(['status' => Penugasan::STATUS_VALIDASI]);

        return response()->json(['status' => true, 'message' => 'Penugasan berhasil disubmit untuk validasi', 'data' => $penugasan]);
    }

    public function validasi(Request $request, string $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);
            $this->authorize('validasi', $penugasan);

            $validated = $request->validate([
                'hasil_validasi' => 'required|in:'.implode(',', [
                    Penugasan::VALIDASI_DITERIMA, Penugasan::VALIDASI_REVISI, Penugasan::VALIDASI_DITOLAK,
                ]),
                'realisasi_persen' => 'required_if:hasil_validasi,'.Penugasan::VALIDASI_DITERIMA.'|nullable|numeric|min:0|max:100',
                'catatan_validasi' => 'nullable|string',
            ]);

            $update = [
                'hasil_validasi' => $validated['hasil_validasi'],
                'catatan_validasi' => $validated['catatan_validasi'] ?? null,
                'validator_id' => $request->user()->id,
                'validated_at' => now(),
            ];

            if ($validated['hasil_validasi'] === Penugasan::VALIDASI_DITERIMA) {
                $update['realisasi_persen'] = $validated['realisasi_persen'];
                $update['status'] = Penugasan::STATUS_SELESAI;
                $update['diterima_at'] = $penugasan->diterima_at ?? now();
            } elseif ($validated['hasil_validasi'] === Penugasan::VALIDASI_REVISI) {
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

            return response()->json(['status' => true, 'message' => 'Validasi berhasil disimpan', 'data' => $penugasan->fresh()]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Penugasan tidak ditemukan', 'data' => null], 404);
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

    private function forbidden()
    {
        return response()->json(['status' => false, 'message' => 'Tidak memiliki izin untuk melakukan aksi ini', 'data' => null], 403);
    }

    private function unprocessable(string $message)
    {
        return response()->json(['status' => false, 'message' => $message, 'data' => null], 422);
    }
}

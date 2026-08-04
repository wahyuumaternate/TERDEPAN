<?php

namespace Modules\Penugasan\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Penugasan\Models\HistoriRevisi;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Models\PerpanjanganWaktu;
use Modules\Penugasan\Models\Progress;

/**
 * Logic inti alur Penugasan, dipakai bersama oleh Api\PenugasanController
 * dan controller web (PenugasanController/TeamController) supaya kedua sisi
 * tidak lagi menduplikasi aturan bisnis secara manual (akar penyebab web
 * pernah drift dari API — lihat docs/plan/08 §4.2).
 */
class PenugasanActionService
{
    public function __construct(
        private readonly HitungKeterlambatan $hitungKeterlambatan,
        private readonly AtasanMandiriEligibility $atasanMandiriEligibility,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public static function aturanBuat(): array
    {
        return [
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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function aturanBuatGrup(): array
    {
        return [
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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function aturanPerbarui(): array
    {
        return [
            'nama_tugas' => 'sometimes|string|max:255',
            'deskripsi' => 'sometimes|string',
            'alasan_penugasan' => 'nullable|string',
            'tanggal_mulai' => 'sometimes|date',
            'tanggal_selesai' => 'sometimes|date|after_or_equal:tanggal_mulai',
            'target_value' => 'nullable|numeric|min:0',
            'satuan' => 'nullable|string|max:30',
            'bobot_persen' => 'sometimes|numeric|min:0|max:100',
            'prioritas' => 'sometimes|in:'.implode(',', Penugasan::PRIORITASES),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function aturanNilai(): array
    {
        return [
            'realisasi_persen' => 'required|numeric|min:0|max:100',
            'bobot_persen' => 'sometimes|numeric|min:0|max:100',
            'catatan_validasi' => 'nullable|string',
        ];
    }

    /**
     * Buat satu penugasan (mandiri atau ditugaskan atasan ke satu pegawai).
     *
     * @param  array<string, mixed>  $validated
     */
    public function buat(array $validated, User $pembuat): Penugasan
    {
        $isSelfInitiated = ((int) $validated['pegawai_id']) === $pembuat->id;
        $pemberiTugasId = $pembuat->id;

        if ($isSelfInitiated) {
            if (empty($validated['atasan_id'])) {
                throw ValidationException::withMessages([
                    'atasan_id' => 'Pilih salah satu atasan yang berhak menyetujui tugas mandiri ini.',
                ]);
            }

            $atasan = User::findOrFail($validated['atasan_id']);

            if (! $this->atasanMandiriEligibility->bolehDipilih($pembuat, $atasan)) {
                throw ValidationException::withMessages([
                    'atasan_id' => 'Atasan yang dipilih tidak berhak menyetujui tugas mandiri Anda.',
                ]);
            }

            $pemberiTugasId = $atasan->id;
        } else {
            Gate::forUser($pembuat)->authorize('create', Penugasan::class);
            $target = User::findOrFail($validated['pegawai_id']);
            Gate::forUser($pembuat)->authorize('assignTo', [Penugasan::class, $target]);
        }

        return Penugasan::create([
            ...$validated,
            'pemberi_tugas_id' => $pemberiTugasId,
            'is_mandiri' => $isSelfInitiated,
            'deadline_terbaru' => $validated['tanggal_selesai'],
            'status' => Penugasan::STATUS_PENDING,
            'status_approval' => $isSelfInitiated ? Penugasan::APPROVAL_PENDING : null,
        ]);
    }

    /**
     * Beri satu tugas ke lebih dari satu pegawai sekaligus (kolektif/per_orang).
     *
     * @param  array<string, mixed>  $validated
     * @return Collection<int, Penugasan>
     */
    public function buatGrup(array $validated, User $pembuat): Collection
    {
        if ($validated['mode_grup'] === Penugasan::MODE_GRUP_KOLEKTIF
            && ! in_array((int) $validated['koordinator_id'], array_map('intval', $validated['pegawai_ids']), true)) {
            throw ValidationException::withMessages([
                'koordinator_id' => 'Koordinator harus salah satu dari pegawai_ids.',
            ]);
        }

        Gate::forUser($pembuat)->authorize('create', Penugasan::class);

        foreach ($validated['pegawai_ids'] as $pegawaiId) {
            Gate::forUser($pembuat)->authorize('assignTo', [Penugasan::class, User::findOrFail($pegawaiId)]);
        }

        $grupId = (string) Str::uuid();

        return collect($validated['pegawai_ids'])->map(function ($pegawaiId) use ($validated, $pembuat, $grupId) {
            return Penugasan::create([
                'pegawai_id' => $pegawaiId,
                'pemberi_tugas_id' => $pembuat->id,
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
        })->values();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function perbarui(Penugasan $penugasan, array $validated, User $actor): Penugasan
    {
        Gate::forUser($actor)->authorize('update', $penugasan);

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

        return $penugasan;
    }

    public function hapus(Penugasan $penugasan, User $actor): void
    {
        Gate::forUser($actor)->authorize('delete', $penugasan);
        $penugasan->delete();
    }

    public function terima(Penugasan $penugasan, User $actor): Penugasan
    {
        Gate::forUser($actor)->authorize('terima', $penugasan);
        $this->tolakJikaBukanKoordinatorGrup($penugasan, 'Hanya koordinator grup yang bisa menerima tugas ini');

        if ($penugasan->status !== Penugasan::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'status' => 'Penugasan hanya bisa diterima jika berstatus pending',
            ]);
        }

        $update = ['status' => Penugasan::STATUS_PROSES, 'diterima_at' => now()];
        $penugasan->update($update);
        $this->cascadeKeGrup($penugasan, $update);

        return $penugasan;
    }

    public function tolak(Penugasan $penugasan, User $actor, string $alasanPenolakan): void
    {
        Gate::forUser($actor)->authorize('tolak', $penugasan);
        $this->tolakJikaBukanKoordinatorGrup($penugasan, 'Hanya koordinator grup yang bisa menolak tugas ini');

        if ($penugasan->status !== Penugasan::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'status' => 'Hanya penugasan berstatus pending yang bisa ditolak',
            ]);
        }

        $penugasan->update(['alasan_reject' => $alasanPenolakan]);

        if ($penugasan->mode_grup === Penugasan::MODE_GRUP_KOLEKTIF) {
            $penugasan->grupAnggota->each->delete();
        }

        $penugasan->delete();
    }

    public function submit(Penugasan $penugasan, User $actor): Penugasan
    {
        Gate::forUser($actor)->authorize('submit', $penugasan);
        $this->tolakJikaBukanKoordinatorGrup($penugasan, 'Hanya koordinator grup yang bisa mengajukan Selesai untuk tugas ini');

        if (! in_array($penugasan->status, [Penugasan::STATUS_PROSES, Penugasan::STATUS_REVISI, Penugasan::STATUS_TERLAMBAT], true)) {
            throw ValidationException::withMessages([
                'status' => 'Penugasan hanya bisa diajukan Selesai dari status proses, revisi, atau terlambat',
            ]);
        }

        if ($penugasan->attachedFiles()->count() === 0) {
            throw ValidationException::withMessages([
                'bukti' => 'Harap upload bukti pengerjaan terlebih dahulu',
            ]);
        }

        $selesaiAt = now();
        $update = ['status' => Penugasan::STATUS_SELESAI, 'tanggal_diselesaikan' => $selesaiAt];
        $penugasan->update($update);
        $this->cascadeKeGrup($penugasan, $update);

        return $penugasan;
    }

    /**
     * Atasan mengisi realisasi tugas berstatus Selesai yang belum pernah dinilai.
     *
     * @param  array<string, mixed>  $validated
     */
    public function nilai(Penugasan $penugasan, array $validated, User $actor): Penugasan
    {
        Gate::forUser($actor)->authorize('nilai', $penugasan);

        if ($penugasan->bobot_persen === null && ! array_key_exists('bobot_persen', $validated)) {
            throw ValidationException::withMessages([
                'bobot_persen' => 'Bobot belum pernah diisi, wajib disertakan saat menilai.',
            ]);
        }

        $bobotPersen = (float) ($validated['bobot_persen'] ?? $penugasan->bobot_persen);
        $realisasiPersen = (float) $validated['realisasi_persen'];

        $deadline = $penugasan->deadline_terbaru ?? $penugasan->tanggal_selesai;
        $tanggalDiselesaikan = $penugasan->tanggal_diselesaikan ?? now();
        $persentaseTerlambat = $this->hitungKeterlambatan->persentase($deadline, $tanggalDiselesaikan);

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
            'validator_id' => $actor->id,
            'validated_at' => now(),
        ];

        $penugasan->update($update);
        $this->cascadeKeGrup($penugasan, $update);

        return $penugasan->fresh();
    }

    /**
     * Atasan memberi revisi pasca-Selesai, hanya sebelum dinilai (aturan E8).
     *
     * @param  array<string, mixed>  $validated
     */
    public function revisi(Penugasan $penugasan, array $validated, User $actor): Penugasan
    {
        Gate::forUser($actor)->authorize('revisi', $penugasan);

        $revisiKe = HistoriRevisi::where('penugasan_id', $penugasan->id)->max('revisi_ke');
        HistoriRevisi::create([
            'penugasan_id' => $penugasan->id,
            'revisi_ke' => ($revisiKe ?? 0) + 1,
            'tanggal_revisi' => now(),
            'catatan_revisi' => $validated['catatan_revisi'],
            'deadline_revisi' => $validated['deadline_baru'],
            'direvisi_oleh' => $actor->id,
            'pegawai_id' => $penugasan->pegawai_id,
        ]);

        $update = [
            'status' => Penugasan::STATUS_REVISI,
            'deadline_terbaru' => $validated['deadline_baru'],
            'tanggal_diselesaikan' => null,
        ];

        $penugasan->update($update);
        $this->cascadeKeGrup($penugasan, $update);

        return $penugasan->fresh();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function approveMandiri(Penugasan $penugasan, array $validated, User $actor): Penugasan
    {
        Gate::forUser($actor)->authorize('approveMandiri', $penugasan);

        if ($penugasan->status !== Penugasan::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'status' => 'Tugas mandiri ini sudah diproses sebelumnya',
            ]);
        }

        $penugasan->update([
            ...$validated,
            'status' => Penugasan::STATUS_PROSES,
            'status_approval' => Penugasan::APPROVAL_DITERIMA,
            'diterima_at' => now(),
        ]);

        return $penugasan->fresh();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function rejectMandiri(Penugasan $penugasan, array $validated, User $actor): Penugasan
    {
        Gate::forUser($actor)->authorize('rejectMandiri', $penugasan);

        if ($penugasan->status !== Penugasan::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'status' => 'Tugas mandiri ini sudah diproses sebelumnya',
            ]);
        }

        $penugasan->update([
            'status' => Penugasan::STATUS_DITOLAK,
            'status_approval' => Penugasan::APPROVAL_DITOLAK,
            'alasan_reject' => $validated['alasan_reject'],
        ]);

        return $penugasan->fresh();
    }

    /**
     * Pegawai mengajukan perpanjangan waktu (aturan E7), maks. 3x disetujui.
     *
     * @param  array<string, mixed>  $validated
     */
    public function ajukanPerpanjangan(Penugasan $penugasan, array $validated, User $actor): PerpanjanganWaktu
    {
        Gate::forUser($actor)->authorize('ajukanPerpanjangan', $penugasan);

        $jumlahDisetujui = $penugasan->perpanjanganWaktu()->where('status', PerpanjanganWaktu::STATUS_DISETUJUI)->count();

        if ($jumlahDisetujui >= 3) {
            throw ValidationException::withMessages([
                'deadline_diminta' => 'Batas perpanjangan waktu (3x) sudah tercapai',
            ]);
        }

        return PerpanjanganWaktu::create([
            'penugasan_id' => $penugasan->id,
            'pegawai_id' => $penugasan->pegawai_id,
            'deadline_lama' => $penugasan->deadline_terbaru ?? $penugasan->tanggal_selesai,
            'deadline_diminta' => $validated['deadline_diminta'],
            'alasan_pengajuan' => $validated['alasan_pengajuan'],
            'status' => PerpanjanganWaktu::STATUS_MENUNGGU,
            'ke_berapa' => $jumlahDisetujui + 1,
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function putuskanPerpanjangan(Penugasan $penugasan, string $perpanjanganId, bool $disetujui, array $validated, User $actor): PerpanjanganWaktu
    {
        Gate::forUser($actor)->authorize('putuskanPerpanjangan', $penugasan);

        $pengajuan = $penugasan->perpanjanganWaktu()->where('id', $perpanjanganId)->firstOrFail();

        if ($pengajuan->status !== PerpanjanganWaktu::STATUS_MENUNGGU) {
            throw ValidationException::withMessages([
                'status' => 'Pengajuan perpanjangan ini sudah diputuskan sebelumnya',
            ]);
        }

        if ($disetujui) {
            $pengajuan->update([
                'status' => PerpanjanganWaktu::STATUS_DISETUJUI,
                'deadline_disetujui' => $validated['deadline_disetujui'],
                'catatan_atasan' => $validated['catatan_atasan'] ?? null,
                'disetujui_oleh_id' => $actor->id,
            ]);

            $update = ['deadline_terbaru' => $validated['deadline_disetujui']];
            $penugasan->update($update);
            $this->cascadeKeGrup($penugasan, $update);
        } else {
            $pengajuan->update([
                'status' => PerpanjanganWaktu::STATUS_DITOLAK,
                'catatan_atasan' => $validated['catatan_atasan'] ?? null,
                'disetujui_oleh_id' => $actor->id,
            ]);
        }

        return $pengajuan->fresh();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updateProgress(Penugasan $penugasan, array $validated, User $actor): Penugasan
    {
        if ($actor->id !== $penugasan->pegawai_id) {
            throw new AuthorizationException('Tidak memiliki izin untuk melakukan aksi ini');
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

        return $penugasan->fresh();
    }

    /**
     * Mode Kolektif: hanya koordinator yang boleh bertindak atas nama grup (§9.1 poin 4).
     */
    private function tolakJikaBukanKoordinatorGrup(Penugasan $penugasan, string $pesan): void
    {
        if ($penugasan->mode_grup === Penugasan::MODE_GRUP_KOLEKTIF && ! $penugasan->is_koordinator) {
            throw new AuthorizationException($pesan);
        }
    }

    /**
     * Cascade perubahan status/nilai/deadline ke seluruh anggota grup pada mode Kolektif.
     *
     * @param  array<string, mixed>  $attrs
     */
    private function cascadeKeGrup(Penugasan $penugasan, array $attrs): void
    {
        if ($penugasan->mode_grup === Penugasan::MODE_GRUP_KOLEKTIF) {
            $penugasan->grupAnggota()->update($attrs);
        }
    }
}

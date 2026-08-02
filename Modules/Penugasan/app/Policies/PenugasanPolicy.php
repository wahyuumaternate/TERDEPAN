<?php

namespace Modules\Penugasan\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Penugasan\Models\Penugasan;

class PenugasanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any penugasan (list)
     */
    public function viewAny(User $user): bool
    {
        // Semua user yang authenticated bisa melihat daftar tugas
        return true;
    }

    /**
     * Determine if user can view specific penugasan
     *
     * Bisa dilihat oleh: pegawai penerima tugas, pemberi tugas, atasan langsung pegawai
     */
    public function view(User $user, Penugasan $tugas): bool
    {
        return $user->id === $tugas->pegawai_id
            || $user->id === $tugas->pemberi_tugas_id
            || $user->id === $tugas->pegawai->profile?->atasan_langsung_id;
    }

    /**
     * Determine if user can create penugasan
     * Hanya jabatan struktural yang bisa memberikan tugas (Admin bisa buat untuk siapa saja)
     */
    public function create(User $user): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID', 'KASUBAG']);
    }

    /**
     * Determine if user berhak memberikan tugas ke pegawai tertentu
     */
    public function assignTo(User $user, User $targetPegawai): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return $targetPegawai->profile?->jabatan?->kode !== 'GATEK';
        }

        if ($kodeJabatan === 'KABID') {
            return $user->profile?->bidang_id === $targetPegawai->profile?->bidang_id
                || $targetPegawai->profile?->jabatan?->kode === 'GATEK';
        }

        if ($kodeJabatan === 'KASUBAG') {
            return $targetPegawai->profile?->atasan_langsung_id === $user->id
                || $targetPegawai->profile?->jabatan?->kode === 'GATEK';
        }

        return $targetPegawai->profile?->atasan_langsung_id === $user->id;
    }

    /**
     * Determine if user can update penugasan
     * Pemberi tugas bisa edit selama Pending atau Proses (aturan E10 mengizinkan
     * perubahan prioritas di kedua status ini, lewat endpoint update yang sama).
     * Untuk tugas mandiri yang Ditolak, pegawai pemiliknya sendiri juga boleh edit
     * (aturan E3: ubah lalu ajukan ulang).
     */
    public function update(User $user, Penugasan $tugas): bool
    {
        if ($user->id === $tugas->pemberi_tugas_id
            && in_array($tugas->status, [Penugasan::STATUS_PENDING, Penugasan::STATUS_PROSES], true)) {
            return true;
        }

        return $tugas->is_mandiri
            && $user->id === $tugas->pegawai_id
            && $tugas->status === Penugasan::STATUS_DITOLAK;
    }

    /**
     * Determine if user can delete penugasan
     */
    public function delete(User $user, Penugasan $tugas): bool
    {
        if ($user->id === $tugas->pemberi_tugas_id && $tugas->status === Penugasan::STATUS_PENDING) {
            return true;
        }

        return $tugas->is_mandiri
            && $user->id === $tugas->pegawai_id
            && $tugas->status === Penugasan::STATUS_DITOLAK;
    }

    /**
     * Determine if user can accept task (terima)
     * Tidak berlaku untuk tugas mandiri — gerbangnya approveMandiri() oleh atasan.
     */
    public function terima(User $user, Penugasan $tugas): bool
    {
        return ! $tugas->is_mandiri
            && $user->id === $tugas->pegawai_id
            && $tugas->status === Penugasan::STATUS_PENDING;
    }

    /**
     * Determine if user can reject task (tolak)
     * Tidak berlaku untuk tugas mandiri — gerbangnya rejectMandiri() oleh atasan.
     */
    public function tolak(User $user, Penugasan $tugas): bool
    {
        return ! $tugas->is_mandiri
            && $user->id === $tugas->pegawai_id
            && $tugas->status === Penugasan::STATUS_PENDING;
    }

    /**
     * Determine if user can submit task (menuju status Selesai)
     */
    public function submit(User $user, Penugasan $tugas): bool
    {
        if ($user->id !== $tugas->pegawai_id) {
            return false;
        }

        return in_array($tugas->status, [Penugasan::STATUS_PROSES, Penugasan::STATUS_REVISI, Penugasan::STATUS_TERLAMBAT]);
    }

    /**
     * Determine if user can upload eviden kinerja
     */
    public function uploadEviden(User $user, Penugasan $tugas): bool
    {
        if ($user->id !== $tugas->pegawai_id) {
            return false;
        }

        return in_array($tugas->status, [Penugasan::STATUS_PROSES, Penugasan::STATUS_REVISI, Penugasan::STATUS_TERLAMBAT]);
    }

    /**
     * Determine if user can menilai tugas (isi realisasi -> nilai akhir final)
     * Hanya berlaku pada tugas Selesai yang belum pernah dinilai (realisasi_persen masih NULL).
     */
    public function nilai(User $user, Penugasan $tugas): bool
    {
        return $user->id === $tugas->pemberi_tugas_id
            && $tugas->status === Penugasan::STATUS_SELESAI
            && $tugas->realisasi_persen === null;
    }

    /**
     * Determine if user can memberi revisi pasca-Selesai (aturan E8)
     * Tidak berlaku lagi begitu tugas sudah dinilai.
     */
    public function revisi(User $user, Penugasan $tugas): bool
    {
        return $user->id === $tugas->pemberi_tugas_id
            && $tugas->status === Penugasan::STATUS_SELESAI
            && $tugas->realisasi_persen === null;
    }

    /**
     * Determine if user can approve tugas mandiri (self-initiated)
     * Atasan penyetuju eksplisit dipilih pegawai saat mengajukan (pemberi_tugas_id),
     * bukan selalu atasan langsung.
     */
    public function approveMandiri(User $user, Penugasan $tugas): bool
    {
        return $tugas->is_mandiri
            && $user->id === $tugas->pemberi_tugas_id
            && $tugas->status_approval === Penugasan::APPROVAL_PENDING;
    }

    /**
     * Determine if user can reject tugas mandiri (self-initiated)
     */
    public function rejectMandiri(User $user, Penugasan $tugas): bool
    {
        return $tugas->is_mandiri
            && $user->id === $tugas->pemberi_tugas_id
            && $tugas->status_approval === Penugasan::APPROVAL_PENDING;
    }

    /**
     * Determine if pegawai can mengajukan perpanjangan waktu
     */
    public function ajukanPerpanjangan(User $user, Penugasan $tugas): bool
    {
        return $user->id === $tugas->pegawai_id
            && in_array($tugas->status, [Penugasan::STATUS_PROSES, Penugasan::STATUS_REVISI]);
    }

    /**
     * Determine if atasan can menyetujui/menolak perpanjangan waktu
     */
    public function putuskanPerpanjangan(User $user, Penugasan $tugas): bool
    {
        return $user->id === $tugas->pemberi_tugas_id;
    }
}

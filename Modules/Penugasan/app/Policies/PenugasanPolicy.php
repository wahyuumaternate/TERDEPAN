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
     * Hanya pemberi tugas yang bisa edit (jika status masih pending)
     */
    public function update(User $user, Penugasan $tugas): bool
    {
        return $user->id === $tugas->pemberi_tugas_id && $tugas->status === Penugasan::STATUS_PENDING;
    }

    /**
     * Determine if user can delete penugasan
     */
    public function delete(User $user, Penugasan $tugas): bool
    {
        return $user->id === $tugas->pemberi_tugas_id && $tugas->status === Penugasan::STATUS_PENDING;
    }

    /**
     * Determine if user can accept task (terima)
     */
    public function terima(User $user, Penugasan $tugas): bool
    {
        return $user->id === $tugas->pegawai_id && $tugas->status === Penugasan::STATUS_PENDING;
    }

    /**
     * Determine if user can reject task (tolak)
     */
    public function tolak(User $user, Penugasan $tugas): bool
    {
        return $user->id === $tugas->pegawai_id && $tugas->status === Penugasan::STATUS_PENDING;
    }

    /**
     * Determine if user can submit task for validation
     */
    public function submit(User $user, Penugasan $tugas): bool
    {
        if ($user->id !== $tugas->pegawai_id) {
            return false;
        }

        return in_array($tugas->status, [Penugasan::STATUS_DIKERJAKAN, Penugasan::STATUS_REVISI]);
    }

    /**
     * Determine if user can upload eviden kinerja
     */
    public function uploadEviden(User $user, Penugasan $tugas): bool
    {
        if ($user->id !== $tugas->pegawai_id) {
            return false;
        }

        return in_array($tugas->status, [Penugasan::STATUS_DIKERJAKAN, Penugasan::STATUS_REVISI]);
    }

    /**
     * Determine if user can validate tugas (atasan pemberi tugas, status: validasi)
     */
    public function validasi(User $user, Penugasan $tugas): bool
    {
        return $user->id === $tugas->pemberi_tugas_id && $tugas->status === Penugasan::STATUS_VALIDASI;
    }

    /**
     * Determine if user can approve/reject tugas mandiri (self-initiated)
     */
    public function approveMandiri(User $user, Penugasan $tugas): bool
    {
        return $tugas->is_mandiri
            && $user->id === $tugas->pegawai->profile?->atasan_langsung_id
            && $tugas->status_approval === Penugasan::APPROVAL_PENDING;
    }
}

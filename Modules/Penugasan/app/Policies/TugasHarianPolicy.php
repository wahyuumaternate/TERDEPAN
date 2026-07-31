<?php

namespace Modules\Penugasan\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Penugasan\Models\TugasHarian;

class TugasHarianPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any tugas harian (list)
     */
    public function viewAny(User $user): bool
    {
        // Semua user yang authenticated bisa melihat daftar tugas
        return true;
    }

    /**
     * Determine if user can view specific tugas harian
     */
    public function view(User $user, TugasHarian $tugas): bool
    {
        // Bisa dilihat oleh:
        // 1. Pegawai penerima tugas
        // 2. Atasan pemberi tugas
        // 3. Atasan langsung pegawai
        return $user->id === $tugas->pegawai_id
            || $user->id === $tugas->pemberi_tugas_id
            || $user->id === $tugas->pegawai->profile?->atasan_langsung_id;
    }

    /**
     * Determine if user can create tugas harian
     * Hanya atasan yang bisa memberikan tugas
     */
    public function create(User $user): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        // Semua jabatan struktural bisa memberikan tugas
        // Kecuali PELAKSANA, JAFUNG, GATEK
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID', 'KASUBAG']);
    }

    /**
     * Determine if user can update tugas harian
     * Hanya atasan pemberi tugas yang bisa edit (jika status masih pending)
     */
    public function update(User $user, TugasHarian $tugas): bool
    {
        // Hanya pemberi tugas yang bisa edit
        if ($user->id !== $tugas->pemberi_tugas_id) {
            return false;
        }

        // Hanya bisa edit jika status masih pending
        return $tugas->status === 'pending';
    }

    /**
     * Determine if user can delete tugas harian
     * Hanya atasan pemberi tugas yang bisa hapus (jika status masih pending)
     */
    public function delete(User $user, TugasHarian $tugas): bool
    {
        // Sama dengan update - hanya pemberi tugas dan status pending
        return $user->id === $tugas->pemberi_tugas_id && $tugas->status === 'pending';
    }

    /**
     * Determine if user can update status tugas (terima/tolak, mulai kerjakan)
     * Hanya pegawai penerima tugas
     */
    public function updateStatus(User $user, TugasHarian $tugas): bool
    {
        return $user->id === $tugas->pegawai_id;
    }

    /**
     * Determine if user can upload eviden kinerja
     * Hanya pegawai penerima tugas (status: dikerjakan atau revisi)
     */
    public function uploadEviden(User $user, TugasHarian $tugas): bool
    {
        if ($user->id !== $tugas->pegawai_id) {
            return false;
        }

        return in_array($tugas->status, ['dikerjakan', 'revisi']);
    }

    /**
     * Determine if user can validate tugas
     * Hanya atasan pemberi tugas (status: validasi)
     */
    public function validate(User $user, TugasHarian $tugas): bool
    {
        if ($user->id !== $tugas->pemberi_tugas_id) {
            return false;
        }

        return $tugas->status === 'validasi';
    }

    /**
     * Determine if user can give revision notes
     * Hanya atasan pemberi tugas (saat validasi)
     */
    public function revise(User $user, TugasHarian $tugas): bool
    {
        return $this->validate($user, $tugas);
    }

    /**
     * Determine if user can give rating/nilai
     * Hanya atasan pemberi tugas (status: selesai)
     */
    public function rate(User $user, TugasHarian $tugas): bool
    {
        if ($user->id !== $tugas->pemberi_tugas_id) {
            return false;
        }

        return $tugas->status === 'selesai';
    }

    /**
     * Determine if user can accept task (terima)
     * Hanya pegawai penerima tugas (status: pending)
     */
    public function terima(User $user, TugasHarian $tugas): bool
    {
        if ($user->id !== $tugas->pegawai_id) {
            return false;
        }

        return $tugas->status === 'pending';
    }

    /**
     * Determine if user can reject task (tolak)
     * Hanya pegawai penerima tugas (status: pending)
     */
    public function tolak(User $user, TugasHarian $tugas): bool
    {
        if ($user->id !== $tugas->pegawai_id) {
            return false;
        }

        return $tugas->status === 'pending';
    }

    /**
     * Determine if user can submit task for validation
     * Hanya pegawai penerima tugas (status: dikerjakan atau revisi)
     */
    public function submit(User $user, TugasHarian $tugas): bool
    {
        if ($user->id !== $tugas->pegawai_id) {
            return false;
        }

        return in_array($tugas->status, ['dikerjakan', 'revisi']);
    }
}

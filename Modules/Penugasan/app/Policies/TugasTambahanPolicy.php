<?php

namespace Modules\Penugasan\Policies;

use App\Models\MasterPegawai;
use Modules\Penugasan\Models\TugasTambahan;
use Illuminate\Auth\Access\HandlesAuthorization;

class TugasTambahanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any tugas tambahan (list)
     */
    public function viewAny(MasterPegawai $user): bool
    {
        // Semua user yang authenticated bisa melihat daftar tugas
        return true;
    }

    /**
     * Determine if user can view specific tugas tambahan
     */
    public function view(MasterPegawai $user, TugasTambahan $tugas): bool
    {
        // Bisa dilihat oleh:
        // 1. Pegawai penerima tugas
        // 2. Atasan pemberi tugas (jika ada)
        // 3. Atasan langsung pegawai

        // Untuk tugas tambahan yang dibuat sendiri (pemberi_tugas_id null)
        if (!$tugas->pemberi_tugas_id) {
            return $user->id === $tugas->pegawai_id
                || $user->id === $tugas->pegawai->atasan_langsung_id;
        }

        // Untuk tugas tambahan dari atasan
        return $user->id === $tugas->pegawai_id
            || $user->id === $tugas->pemberi_tugas_id
            || $user->id === $tugas->pegawai->atasan_langsung_id;
    }
    /**
     * Determine if user can create tugas tambahan
     * Atasan langsung bisa memberikan tugas tambahan ke bawahan
     */
    public function create(MasterPegawai $user): bool
    {
        $kodeJabatan = $user->jabatan?->kode;

        // Semua jabatan struktural bisa memberikan tugas tambahan
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID', 'KASUBAG']);
    }

    /**
     * Determine if user can update tugas tambahan
     * Hanya atasan pemberi tugas yang bisa edit (jika status masih pending)
     */
    public function update(MasterPegawai $user, TugasTambahan $tugas): bool
    {
        // Hanya pemberi tugas yang bisa edit (status pending)
        return $user->id === $tugas->pemberi_tugas_id && $tugas->status === 'pending';
    }

    /**
     * Determine if user can delete tugas tambahan
     */
    public function delete(MasterPegawai $user, TugasTambahan $tugas): bool
    {
        // Hanya pemberi tugas yang bisa delete (status pending)
        return $user->id === $tugas->pemberi_tugas_id && $tugas->status === 'pending';
    }

    /**
     * Determine if user can update status tugas (terima/tolak, mulai kerjakan)
     * Hanya pegawai penerima tugas
     */
    public function updateStatus(MasterPegawai $user, TugasTambahan $tugas): bool
    {
        return $user->id === $tugas->pegawai_id;
    }

    /**
     * Determine if user can upload eviden kinerja
     * Hanya pegawai penerima tugas (status: dikerjakan atau revisi)
     */
    public function uploadEviden(MasterPegawai $user, TugasTambahan $tugas): bool
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
    public function validate(MasterPegawai $user, TugasTambahan $tugas): bool
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
    public function revise(MasterPegawai $user, TugasTambahan $tugas): bool
    {
        return $this->validate($user, $tugas);
    }

    /**
     * Determine if user can give rating/nilai
     * Hanya atasan pemberi tugas (status: selesai)
     */
    public function rate(MasterPegawai $user, TugasTambahan $tugas): bool
    {
        if ($user->id !== $tugas->pemberi_tugas_id) {
            return false;
        }

        return $tugas->status === 'selesai';
    }
}

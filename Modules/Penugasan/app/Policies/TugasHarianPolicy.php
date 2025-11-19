<?php

namespace Modules\Penugasan\Policies;

use App\Models\MasterPegawai;
use Modules\Penugasan\Models\TugasHarian;
use Illuminate\Auth\Access\HandlesAuthorization;

class TugasHarianPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any tugas harian (list)
     */
    public function viewAny(MasterPegawai $user): bool
    {
        // Semua user yang authenticated bisa melihat daftar tugas
        return true;
    }

    /**
     * Determine if user can view specific tugas harian
     */
    public function view(MasterPegawai $user, TugasHarian $tugas): bool
    {
        // Bisa dilihat oleh:
        // 1. Pegawai penerima tugas
        // 2. Atasan pemberi tugas
        // 3. Atasan langsung pegawai
        return $user->id === $tugas->pegawai_id
            || $user->id === $tugas->pemberi_tugas_id
            || $user->id === $tugas->pegawai->atasan_langsung_id;
    }

    /**
     * Determine if user can create tugas harian
     * Hanya atasan yang bisa memberikan tugas
     */
    public function create(MasterPegawai $user): bool
    {
        $kodeJabatan = $user->jabatan?->kode;

        // Semua jabatan struktural bisa memberikan tugas
        // Kecuali PELAKSANA, JAFUNG, GATEK
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID', 'KASUBAG']);
    }

    /**
     * Determine if user can update tugas harian
     * Hanya atasan pemberi tugas yang bisa edit (jika status masih pending)
     */
    public function update(MasterPegawai $user, TugasHarian $tugas): bool
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
    public function delete(MasterPegawai $user, TugasHarian $tugas): bool
    {
        // Sama dengan update - hanya pemberi tugas dan status pending
        return $user->id === $tugas->pemberi_tugas_id && $tugas->status === 'pending';
    }

    /**
     * Determine if user can update status tugas (terima/tolak, mulai kerjakan)
     * Hanya pegawai penerima tugas
     */
    public function updateStatus(MasterPegawai $user, TugasHarian $tugas): bool
    {
        return $user->id === $tugas->pegawai_id;
    }

    /**
     * Determine if user can upload eviden kinerja
     * Hanya pegawai penerima tugas (status: dikerjakan atau revisi)
     */
    public function uploadEviden(MasterPegawai $user, TugasHarian $tugas): bool
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
    public function validate(MasterPegawai $user, TugasHarian $tugas): bool
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
    public function revise(MasterPegawai $user, TugasHarian $tugas): bool
    {
        return $this->validate($user, $tugas);
    }

    /**
     * Determine if user can give rating/nilai
     * Hanya atasan pemberi tugas (status: selesai)
     */
    public function rate(MasterPegawai $user, TugasHarian $tugas): bool
    {
        if ($user->id !== $tugas->pemberi_tugas_id) {
            return false;
        }

        return $tugas->status === 'selesai';
    }
}

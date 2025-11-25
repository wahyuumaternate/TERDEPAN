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
     * Level tertentu (KABAN, KABID) bisa assign lintas hierarki
     */
    public function create(MasterPegawai $user): bool
    {
        $kodeJabatan = $user->jabatan?->kode;

        // Semua jabatan struktural bisa memberikan tugas tambahan
        // KABAN dan KABID bisa assign lintas bidang/hierarki
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID', 'KASUBAG']);
    }

    /**
     * Determine if user can assign task to specific pegawai
     * Mengecek apakah user berhak memberikan tugas ke target pegawai
     */
    public function assignTo(MasterPegawai $user, MasterPegawai $targetPegawai): bool
    {
        $kodeJabatan = $user->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN bisa assign ke siapa saja
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // KABID bisa assign ke pegawai di bidangnya
        if ($kodeJabatan === 'KABID') {
            return $user->bidang_id === $targetPegawai->bidang_id;
        }

        // KASUBAG hanya bisa assign ke bawahan langsung
        if ($kodeJabatan === 'KASUBAG') {
            return $targetPegawai->atasan_langsung_id === $user->id;
        }

        return false;
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

    /**
     * Determine if user can accept task (terima)
     * Hanya pegawai penerima tugas (status: pending)
     */
    public function terima(MasterPegawai $user, TugasTambahan $tugas): bool
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
    public function tolak(MasterPegawai $user, TugasTambahan $tugas): bool
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
    public function submit(MasterPegawai $user, TugasTambahan $tugas): bool
    {
        if ($user->id !== $tugas->pegawai_id) {
            return false;
        }

        return in_array($tugas->status, ['dikerjakan', 'revisi']);
    }
}

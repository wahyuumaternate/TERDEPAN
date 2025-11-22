<?php

namespace Modules\TerminalData\Policies;

use App\Models\MasterPegawai;
use Modules\TerminalData\Models\TdFile;
use Illuminate\Auth\Access\HandlesAuthorization;

class TdFilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any files
     * Semua pegawai bisa lihat file
     */
    public function viewAny(MasterPegawai $user): bool
    {
        return $user->jabatan !== null;
    }

    /**
     * Determine if user can view specific file
     * Semua pegawai bisa lihat file
     */
    public function view(MasterPegawai $user, TdFile $file): bool
    {
        return $user->jabatan !== null;
    }

    /**
     * Determine if user can upload files
     * ADMIN, KABAN, SEKBAN - bisa upload di semua lokasi
     * Pegawai bidang - bisa upload di folder bidangnya
     */
    public function upload(MasterPegawai $user, $folder = null): bool
    {
        // Basic check - user harus punya jabatan
        if (!$user->jabatan) {
            return false;
        }

        // Jika tidak ada folder context, return true (general check)
        if (!$folder) {
            return true;
        }

        $kodeJabatan = $user->jabatan->kode;

        // ADMIN, KABAN, SEKBAN - bisa upload di semua lokasi
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // Pegawai bidang (KABID, KASUBAG, PELAKSANA, JAFUNG, GATEK)
        // Hanya bisa upload di folder bidangnya
        if (in_array($kodeJabatan, ['KABID', 'KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])) {
            return $folder->bidang_id === $user->bidang_id;
        }

        return false;
    }

    /**
     * Determine if user can download file
     * Semua pegawai bisa download file
     */
    public function download(MasterPegawai $user, TdFile $file): bool
    {
        return $user->jabatan !== null;
    }
    /**
     * Determine if user can update file
     * ADMIN, KABAN, SEKBAN - Full Access
     * Pemilik file - bisa update file sendiri
     */
    public function update(MasterPegawai $user, TdFile $file): bool
    {
        $kodeJabatan = $user->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - Full Access
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // Pemilik file bisa update
        return $file->created_by === $user->id;
    }

    /**
     * Determine if user can delete file
     * ADMIN, KABAN, SEKBAN - Full Access
     * Pemilik file - bisa delete file sendiri
     */
    public function delete(MasterPegawai $user, TdFile $file): bool
    {
        $kodeJabatan = $user->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - Full Access
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // Pemilik file bisa delete
        return $file->created_by === $user->id;
    }

    /**
     * Determine if user can move file
     * ADMIN, KABAN, SEKBAN - Full Access
     * Pemilik file - bisa move file sendiri
     */
    public function move(MasterPegawai $user, TdFile $file): bool
    {
        $kodeJabatan = $user->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - Full Access
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // Pemilik file bisa move
        return $file->created_by === $user->id;
    }

    /**
     * Determine if user can view all trashed files
     * ADMIN, KABAN, SEKBAN bisa lihat semua sampah
     * User lain hanya bisa lihat sampah mereka sendiri
     */
    public function viewTrashed(MasterPegawai $user): bool
    {
        $kodeJabatan = $user->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - bisa lihat semua sampah
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can restore file from trash
     * ADMIN, KABAN, SEKBAN - Full Access
     * Pemilik file - bisa restore file sendiri
     */
    public function restore(MasterPegawai $user, TdFile $file): bool
    {
        $kodeJabatan = $user->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - Full Access
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // Pemilik file bisa restore
        return $file->created_by === $user->id;
    }

    /**
     * Determine if user can permanently delete file
     * ADMIN, KABAN, SEKBAN - Full Access
     * Pemilik file - bisa force delete file sendiri
     */
    public function forceDelete(MasterPegawai $user, TdFile $file): bool
    {
        $kodeJabatan = $user->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - Full Access
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // Pemilik file bisa force delete
        return $file->created_by === $user->id;
    }
}

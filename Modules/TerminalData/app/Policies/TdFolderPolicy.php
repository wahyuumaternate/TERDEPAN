<?php

namespace Modules\TerminalData\Policies;

use App\Models\MasterPegawai;
use Modules\TerminalData\Models\TdFolder;
use Illuminate\Auth\Access\HandlesAuthorization;

class TdFolderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any folders
     */
    public function viewAny(MasterPegawai $user): bool
    {
        // Semua pegawai bisa view folders sesuai level mereka
        return $user->jabatan !== null;
    }

    /**
     * Determine if user can view specific folder
     */
    public function view(MasterPegawai $user, TdFolder $folder): bool
    {
        // Semua user yang terautentikasi bisa melihat folder
        return true;
    }

    /**
     * Determine if user can create folders
     */
    public function create(MasterPegawai $user): bool
    {
        // Semua pegawai bisa create folder
        return $user->jabatan !== null;
    }

    public function createInParent(MasterPegawai $user, ?TdFolder $parentFolder): bool
    {
        // Basic check - user harus punya jabatan
        if (!$user->jabatan) {
            return false;
        }

        // Jika tidak ada parent folder, return true (general check)
        if (!$parentFolder) {
            return true;
        }

        $kodeJabatan = $user->jabatan->kode;

        // ADMIN, KABAN, SEKBAN - bisa create di mana saja
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // KABID - hanya bisa create di folder bidangnya
        if (in_array($kodeJabatan, ['KABID', 'KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])) {
            return $parentFolder->bidang_id === $user->bidang_id;
        }

        return false;
    }

    /**
     * Determine if user can update folder (general update)
     */
    public function update(MasterPegawai $user, TdFolder $folder): bool
    {
        $kodeJabatan = $user->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - Full Access
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // KABID - Edit folder bidangnya
        if (in_array($kodeJabatan, ['KABID'])) {
            return $folder->bidang_id === $user->bidang_id;
        }

        // KASUBAG, PELAKSANA, JAFUNG, GATEK - Edit folder sendiri
        if (in_array($kodeJabatan, ['KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])) {
            return $folder->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine if user can rename folder
     * KABID bisa rename semua folder di bidangnya, bukan hanya milik sendiri
     */
    public function rename(MasterPegawai $user, TdFolder $folder): bool
    {
        $kodeJabatan = $user->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - Full Access
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // KABID - Rename semua folder bidangnya
        if (in_array($kodeJabatan, ['KABID'])) {
            return $folder->bidang_id === $user->bidang_id;
        }

        // KASUBAG, PELAKSANA, JAFUNG, GATEK - Rename folder sendiri
        if (in_array($kodeJabatan, ['KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])) {
            return $folder->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine if user can delete folder
     */
    public function delete(MasterPegawai $user, TdFolder $folder): bool
    {
        $kodeJabatan = $user->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - Full Access
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // KABID, KASUBAG, PELAKSANA, JAFUNG, GATEK - Delete folder sendiri
        if (in_array($kodeJabatan, ['KABID', 'KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])) {
            return $folder->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine if user can upload files to folder
     */
    public function upload(MasterPegawai $user, TdFolder $folder): bool
    {
        // Basic check - user harus punya jabatan
        if (!$user->jabatan) {
            return false;
        }

        $kodeJabatan = $user->jabatan->kode;

        // ADMIN, KABAN, SEKBAN - bisa upload di mana saja
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // KABID, KASUBAG, PELAKSANA, JAFUNG, GATEK - hanya bisa upload di folder bidangnya
        if (in_array($kodeJabatan, ['KABID', 'KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])) {
            return $folder->bidang_id === $user->bidang_id;
        }

        return false;
    }

    /**
     * Determine if user can view all trashed folders
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
     * Determine if user can restore folder
     */
    public function restore(MasterPegawai $user, TdFolder $folder): bool
    {
        $kodeJabatan = $user->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - Full Access
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // Pemilik folder bisa restore
        return $folder->created_by === $user->id;
    }

    /**
     * Determine if user can force delete folder
     */
    public function forceDelete(MasterPegawai $user, TdFolder $folder): bool
    {
        $kodeJabatan = $user->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - Full Access
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // Pemilik folder bisa force delete
        return $folder->created_by === $user->id;
    }
}

<?php

namespace Modules\TerminalData\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\TerminalData\Models\TdFolder;

class TdFolderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any folders
     */
    public function viewAny(User $user): bool
    {
        // Semua pegawai bisa view folders sesuai level mereka
        return $user->profile?->jabatan !== null;
    }

    /**
     * Determine if user can view specific folder
     */
    public function view(User $user, TdFolder $folder): bool
    {
        // Semua user yang terautentikasi bisa melihat folder
        return true;
    }

    /**
     * Determine if user can create folders
     */
    public function create(User $user): bool
    {
        // Semua pegawai bisa create folder
        return $user->profile?->jabatan !== null;
    }

    public function createInParent(User $user, ?TdFolder $parentFolder): bool
    {
        // Basic check - user harus punya jabatan
        if (! $user->profile?->jabatan) {
            return false;
        }

        // Jika tidak ada parent folder, return true (general check)
        if (! $parentFolder) {
            return true;
        }

        $kodeJabatan = $user->profile->jabatan->kode;

        // ADMIN, KABAN, SEKBAN - bisa create di mana saja
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // KABID - hanya bisa create di folder bidangnya
        if (in_array($kodeJabatan, ['KABID', 'KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])) {
            return $parentFolder->bidang_id === $user->profile?->bidang_id;
        }

        return false;
    }

    /**
     * Determine if user can update folder (general update)
     */
    public function update(User $user, TdFolder $folder): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - Full Access
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // KABID - Edit folder bidangnya
        if (in_array($kodeJabatan, ['KABID'])) {
            return $folder->bidang_id === $user->profile?->bidang_id;
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
    public function rename(User $user, TdFolder $folder): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - Full Access
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // KABID - Rename semua folder bidangnya
        if (in_array($kodeJabatan, ['KABID'])) {
            return $folder->bidang_id === $user->profile?->bidang_id;
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
    public function delete(User $user, TdFolder $folder): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

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
    public function upload(User $user, TdFolder $folder): bool
    {
        // Basic check - user harus punya jabatan
        if (! $user->profile?->jabatan) {
            return false;
        }

        $kodeJabatan = $user->profile->jabatan->kode;

        // ADMIN, KABAN, SEKBAN - bisa upload di mana saja
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // KABID, KASUBAG, PELAKSANA, JAFUNG, GATEK - hanya bisa upload di folder bidangnya
        if (in_array($kodeJabatan, ['KABID', 'KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])) {
            return $folder->bidang_id === $user->profile?->bidang_id;
        }

        return false;
    }

    /**
     * Determine if user can view all trashed folders
     * ADMIN, KABAN, SEKBAN bisa lihat semua sampah
     * User lain hanya bisa lihat sampah mereka sendiri
     */
    public function viewTrashed(User $user): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - bisa lihat semua sampah
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can restore folder
     */
    public function restore(User $user, TdFolder $folder): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

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
    public function forceDelete(User $user, TdFolder $folder): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - Full Access
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // Pemilik folder bisa force delete
        return $folder->created_by === $user->id;
    }
}

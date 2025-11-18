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

    /**
     * Determine if user can update folder
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
     * Determine if user can delete folder
     */
    public function delete(MasterPegawai $user, TdFolder $folder): bool
    {
        // Tidak bisa hapus folder jika masih ada file di dalamnya
        if ($folder->files()->count() > 0) {
            return false;
        }

        // Tidak bisa hapus folder jika masih ada subfolder
        if ($folder->subfolders()->count() > 0) {
            return false;
        }

        $kodeJabatan = $user->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - Full Access
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return true;
        }

        // KABID - Delete folder bidangnya
        if (in_array($kodeJabatan, ['KABID'])) {
            return $folder->bidang_id === $user->bidang_id;
        }

        // KASUBAG, PELAKSANA, JAFUNG, GATEK - Delete folder sendiri
        if (in_array($kodeJabatan, ['KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])) {
            return $folder->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine if user can restore folder
     */
    public function restore(MasterPegawai $user, TdFolder $folder): bool
    {
        // Bisa restore jika bisa delete
        return $this->delete($user, $folder);
    }

    /**
     * Determine if user can force delete folder
     */
    public function forceDelete(MasterPegawai $user, TdFolder $folder): bool
    {
        $kodeJabatan = $user->jabatan?->kode;

        // Hanya ADMIN, KABAN dan SEKBAN yang bisa force delete
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']) && $this->delete($user, $folder);
    }
}

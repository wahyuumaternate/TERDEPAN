<?php

namespace Modules\TerminalData\Policies;

use App\Models\MasterPegawai;
use Modules\TerminalData\Models\TdFolder;
use Illuminate\Auth\Access\HandlesAuthorization;

class TdFolderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any folders.
     */
    public function viewAny(MasterPegawai $user): bool
    {
        // Semua authenticated user dapat melihat daftar folder
        return true;
    }

    /**
     * Determine if the user can view the folder.
     */
    public function view(MasterPegawai $user, TdFolder $folder): bool
    {
        // Owner dapat melihat
        if ($folder->created_by === $user->id) {
            return true;
        }

        // Public folder dapat dilihat semua orang
        if ($folder->is_public) {
            return true;
        }

        // Folder dalam bidang yang sama dapat dilihat
        if ($folder->bidang_id && $folder->bidang_id === $user->bidang_id) {
            return true;
        }

        // Check if user has shared access
        return $folder->canAccess($user, 'viewer');
    }

    /**
     * Determine if the user can create folders.
     */
    public function create(MasterPegawai $user): bool
    {
        // Semua user dapat membuat folder
        return true;
    }

    /**
     * Determine if the user can update the folder.
     */
    public function update(MasterPegawai $user, TdFolder $folder): bool
    {
        // System folder tidak dapat diupdate
        if ($folder->is_system) {
            return false;
        }

        // Locked folder tidak dapat diupdate
        if ($folder->is_locked) {
            return $folder->created_by === $user->id; // Hanya owner yang bisa unlock
        }

        // Owner dapat update
        if ($folder->created_by === $user->id) {
            return true;
        }

        // Check if user has editor access
        return $folder->canAccess($user, 'editor');
    }

    /**
     * Determine if the user can delete the folder.
     */
    public function delete(MasterPegawai $user, TdFolder $folder): bool
    {
        // System folder tidak dapat dihapus
        if ($folder->is_system) {
            return false;
        }

        // Locked folder tidak dapat dihapus
        if ($folder->is_locked) {
            return false;
        }

        // Hanya owner yang dapat menghapus
        return $folder->created_by === $user->id;
    }

    /**
     * Determine if the user can restore the folder.
     */
    public function restore(MasterPegawai $user, TdFolder $folder): bool
    {
        return $folder->created_by === $user->id;
    }

    /**
     * Determine if the user can permanently delete the folder.
     */
    public function forceDelete(MasterPegawai $user, TdFolder $folder): bool
    {
        // Hanya owner yang dapat force delete
        return $folder->created_by === $user->id;
    }

    /**
     * Determine if the user can share the folder.
     */
    public function share(MasterPegawai $user, TdFolder $folder): bool
    {
        // Owner dapat share
        if ($folder->created_by === $user->id) {
            return true;
        }

        // Check if user has owner access level from sharing
        return $folder->canAccess($user, 'owner');
    }
}

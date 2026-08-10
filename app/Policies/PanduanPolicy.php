<?php

namespace App\Policies;

use App\Models\Panduan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PanduanPolicy
{
    use HandlesAuthorization;

    /**
     * Semua pegawai terautentikasi bisa melihat daftar panduan.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Semua pegawai terautentikasi bisa melihat/preview/download panduan.
     */
    public function view(User $user, Panduan $panduan): bool
    {
        return true;
    }

    /**
     * Hanya ADMIN yang bisa mengelola panduan.
     */
    public function create(User $user): bool
    {
        return $user->profile?->jabatan?->kode === 'ADMIN';
    }

    /**
     * Hanya ADMIN yang bisa mengelola panduan.
     */
    public function update(User $user, Panduan $panduan): bool
    {
        return $user->profile?->jabatan?->kode === 'ADMIN';
    }

    /**
     * Hanya ADMIN yang bisa mengelola panduan.
     */
    public function delete(User $user, Panduan $panduan): bool
    {
        return $user->profile?->jabatan?->kode === 'ADMIN';
    }
}

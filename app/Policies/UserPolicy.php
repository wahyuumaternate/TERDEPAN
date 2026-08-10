<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any pegawai
     * Hanya ADMIN, KABAN, SEKBAN yang bisa melihat master data
     */
    public function viewAny(User $user): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can view specific pegawai
     * Hanya ADMIN, KABAN, SEKBAN yang bisa melihat master data
     */
    public function view(User $user, User $pegawai): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can create pegawai
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function create(User $user): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can update pegawai
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function update(User $user, User $pegawai): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can delete pegawai
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function delete(User $user, User $pegawai): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }
}

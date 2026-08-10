<?php

namespace App\Policies;

use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterJabatanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any jabatan
     * Hanya ADMIN, KABAN, SEKBAN yang bisa melihat master data
     */
    public function viewAny(User $user): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can view specific jabatan
     * Hanya ADMIN, KABAN, SEKBAN yang bisa melihat master data
     */
    public function view(User $user, MasterJabatan $jabatan): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can create jabatan
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function create(User $user): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can update jabatan
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function update(User $user, MasterJabatan $jabatan): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can delete jabatan
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function delete(User $user, MasterJabatan $jabatan): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }
}

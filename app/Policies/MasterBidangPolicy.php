<?php

namespace App\Policies;

use App\Models\MasterBidang;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterBidangPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any bidang
     * Hanya ADMIN, KABAN, SEKBAN yang bisa melihat master data
     */
    public function viewAny(User $user): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can view specific bidang
     * Hanya ADMIN, KABAN, SEKBAN yang bisa melihat master data
     */
    public function view(User $user, MasterBidang $bidang): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can create bidang
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function create(User $user): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can update bidang
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function update(User $user, MasterBidang $bidang): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can delete bidang
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function delete(User $user, MasterBidang $bidang): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }
}

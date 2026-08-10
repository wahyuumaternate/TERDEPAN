<?php

namespace App\Policies;

use App\Models\MasterSubBidang;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterSubBidangPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any sub bidang
     * Hanya ADMIN, KABAN, SEKBAN yang bisa melihat master data
     */
    public function viewAny(User $user): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can view specific sub bidang
     * Hanya ADMIN, KABAN, SEKBAN yang bisa melihat master data
     */
    public function view(User $user, MasterSubBidang $subBidang): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can create sub bidang
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function create(User $user): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can update sub bidang
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function update(User $user, MasterSubBidang $subBidang): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can delete sub bidang
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function delete(User $user, MasterSubBidang $subBidang): bool
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }
}

<?php

namespace App\Policies;

use App\Models\MasterPegawai;
use App\Models\MasterBidang;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterBidangPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any bidang
     * Hanya ADMIN, KABAN, SEKBAN yang bisa melihat master data
     */
    public function viewAny(MasterPegawai $user): bool
    {
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can view specific bidang
     * Hanya ADMIN, KABAN, SEKBAN yang bisa melihat master data
     */
    public function view(MasterPegawai $user, MasterBidang $bidang): bool
    {
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can create bidang
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function create(MasterPegawai $user): bool
    {
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can update bidang
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function update(MasterPegawai $user, MasterBidang $bidang): bool
    {
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can delete bidang
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function delete(MasterPegawai $user, MasterBidang $bidang): bool
    {
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }
}

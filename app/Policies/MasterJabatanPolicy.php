<?php

namespace App\Policies;

use App\Models\MasterPegawai;
use App\Models\MasterJabatan;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterJabatanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any jabatan
     * Hanya ADMIN, KABAN, SEKBAN yang bisa melihat master data
     */
    public function viewAny(MasterPegawai $user): bool
    {
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can view specific jabatan
     * Hanya ADMIN, KABAN, SEKBAN yang bisa melihat master data
     */
    public function view(MasterPegawai $user, MasterJabatan $jabatan): bool
    {
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can create jabatan
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function create(MasterPegawai $user): bool
    {
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can update jabatan
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function update(MasterPegawai $user, MasterJabatan $jabatan): bool
    {
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can delete jabatan
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function delete(MasterPegawai $user, MasterJabatan $jabatan): bool
    {
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }
}

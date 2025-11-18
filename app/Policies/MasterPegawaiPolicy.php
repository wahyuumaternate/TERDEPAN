<?php

namespace App\Policies;

use App\Models\MasterPegawai;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterPegawaiPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any pegawai
     * Hanya ADMIN, KABAN, SEKBAN yang bisa melihat master data
     */
    public function viewAny(MasterPegawai $user): bool
    {
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can view specific pegawai
     * Hanya ADMIN, KABAN, SEKBAN yang bisa melihat master data
     */
    public function view(MasterPegawai $user, MasterPegawai $pegawai): bool
    {
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can create pegawai
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function create(MasterPegawai $user): bool
    {
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can update pegawai
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function update(MasterPegawai $user, MasterPegawai $pegawai): bool
    {
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can delete pegawai
     * Hanya ADMIN, KABAN, SEKBAN yang bisa mengelola master data
     */
    public function delete(MasterPegawai $user, MasterPegawai $pegawai): bool
    {
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }
}

<?php

namespace Modules\PerjanjianKinerja\Policies;

use App\Models\MasterPegawai;
use Modules\PerjanjianKinerja\Models\PkPeriode;
use Illuminate\Auth\Access\HandlesAuthorization;

class PkPeriodePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can manage periode
     */
    public function viewAny(MasterPegawai $user)
    {
        $kodeJabatan = $user->jabatan?->kode;
        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can view periode
     */
    public function view(MasterPegawai $user, PkPeriode $periode)
    {
        return $this->viewAny($user);
    }

    /**
     * Determine if user can create periode
     */
    public function create(MasterPegawai $user)
    {
        return $this->viewAny($user);
    }

    /**
     * Determine if user can update periode
     */
    public function update(MasterPegawai $user, PkPeriode $periode)
    {
        // Cannot update active periode
        if ($periode->is_active) {
            return false;
        }

        return $this->viewAny($user);
    }

    /**
     * Determine if user can delete periode
     */
    public function delete(MasterPegawai $user, PkPeriode $periode)
    {
        // Cannot delete active periode
        if ($periode->is_active) {
            return false;
        }

        // Cannot delete if has PK
        if ($periode->perjanjianKinerja()->count() > 0) {
            return false;
        }

        return $this->viewAny($user);
    }

    /**
     * Determine if user can activate periode
     */
    public function activate(MasterPegawai $user, PkPeriode $periode)
    {
        return $this->viewAny($user);
    }

    /**
     * Determine if user can deactivate periode
     */
    public function deactivate(MasterPegawai $user, PkPeriode $periode)
    {
        return $this->viewAny($user);
    }
}

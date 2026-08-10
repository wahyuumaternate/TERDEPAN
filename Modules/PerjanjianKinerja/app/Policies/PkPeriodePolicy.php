<?php

namespace Modules\PerjanjianKinerja\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\PerjanjianKinerja\Models\PkPeriode;

class PkPeriodePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can manage periode
     */
    public function viewAny(User $user)
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
    }

    /**
     * Determine if user can view periode
     */
    public function view(User $user, PkPeriode $periode)
    {
        return $this->viewAny($user);
    }

    /**
     * Determine if user can create periode
     */
    public function create(User $user)
    {
        return $this->viewAny($user);
    }

    /**
     * Determine if user can update periode
     */
    public function update(User $user, PkPeriode $periode)
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
    public function delete(User $user, PkPeriode $periode)
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
    public function activate(User $user, PkPeriode $periode)
    {
        return $this->viewAny($user);
    }

    /**
     * Determine if user can deactivate periode
     */
    public function deactivate(User $user, PkPeriode $periode)
    {
        return $this->viewAny($user);
    }
}

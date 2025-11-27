<?php

namespace Modules\PerjanjianKinerja\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\PerjanjianKinerja\Models\PkPerjanjianKinerja;
use Modules\PerjanjianKinerja\Models\PkPeriode;
use Modules\PerjanjianKinerja\Policies\PkPerjanjianKinerjaPolicy;
use Modules\PerjanjianKinerja\Policies\PkPeriodePolicy;

class PerjanjianKinerjaAuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        PkPerjanjianKinerja::class => PkPerjanjianKinerjaPolicy::class,
        PkPeriode::class => PkPeriodePolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerGates();
    }

    /**
     * Register the application's policies.
     */
    public function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }

    /**
     * Register custom gates.
     */
    protected function registerGates(): void
    {
        // Gate untuk akses menu
        Gate::define('view-pk-menu', function ($user) {
            // Semua pegawai aktif bisa lihat menu PK
            return $user->status_aktif === 'Aktif';
        });

        Gate::define('manage-periode', function ($user) {
            $kodeJabatan = $user->jabatan?->kode;
            return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
        });

        Gate::define('manage-template', function ($user) {
            $kodeJabatan = $user->jabatan?->kode;
            return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
        });

        Gate::define('view-all-pk', function ($user) {
            $kodeJabatan = $user->jabatan?->kode;
            return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID']);
        });

        Gate::define('validate-pk', function ($user) {
            // Hanya KABAN, SEKBAN, KABID yang bisa validasi PK
            $kodeJabatan = $user->jabatan?->kode;
            return in_array($kodeJabatan, ['KABAN', 'SEKBAN', 'KABID']);
        });

        Gate::define('create-pk-for-others', function ($user) {
            $kodeJabatan = $user->jabatan?->kode;
            return in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID']);
        });
    }
}

<?php

namespace App\Providers;

use App\Helpers\StorageHelper;
use App\Models\User;
use App\Services\NomorDokumenService;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Penugasan\Models\HistoriRevisi;
use Modules\Penugasan\Models\Penugasan;
use Modules\Penugasan\Models\Progress;
use Modules\TerminalData\Models\TdFile;
use Modules\TerminalData\Models\TdFolder;
use Modules\TerminalData\Policies\TdFilePolicy;
use Modules\TerminalData\Policies\TdFolderPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register NomorDokumenService sebagai singleton
        $this->app->singleton(NomorDokumenService::class, function ($app) {
            return new NomorDokumenService;
        });
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        // Morph map stabil untuk Spatie Permission (model_has_roles/model_has_permissions)
        // dan untuk relasi polymorphic attachable (td_files), supaya tidak terikat ke nama
        // class penuh yang bisa berubah lagi ke depannya.
        Relation::enforceMorphMap([
            'user' => User::class,
            'penugasan' => Penugasan::class,
            'penugasan_progress' => Progress::class,
            'penugasan_histori_revisi' => HistoriRevisi::class,
            'td_folder' => TdFolder::class,
            'td_file' => TdFile::class,
        ]);

        // Register policies
        Gate::policy(TdFolder::class, TdFolderPolicy::class);
        Gate::policy(TdFile::class, TdFilePolicy::class);

        // Hitung total & penggunaan storage di disk 'public'
        $used = StorageHelper::getFolderSize(); // tanpa argumen, default ke public
        $total = 1000 * 1024 * 1024 * 1024; //  total kapasitas: 800 GB
        // $total = 200 * 1024 * 1024; // total kapasitas: 200 MB
        $percentage = ($used / $total) * 100;

        // Helper untuk format tanggal yang aman dari null
        View::share('formatDate', function ($date, $format = 'd M Y H:i', $default = 'Tidak diketahui') {
            return $date ? $date->format($format) : $default;
        });

        // Bagikan ke semua view
        View::share([
            'used' => StorageHelper::formatBytes($used),
            'total' => StorageHelper::formatBytes($total),
            'percentage' => round($percentage, 2),
        ]);
    }
}

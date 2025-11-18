<?php

namespace App\Providers;

use App\Helpers\StorageHelper;
use App\Services\NomorDokumenService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\TerminalData\Models\TdFolder;
use Modules\TerminalData\Models\TdFile;
use Modules\TerminalData\Policies\TdFolderPolicy;
use Modules\TerminalData\Policies\TdFilePolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register NomorDokumenService sebagai singleton
        $this->app->singleton(NomorDokumenService::class, function ($app) {
            return new NomorDokumenService();
        });
        //
    }

    /**
     * Bootstrap any application services.
     */

    public function boot(): void
    {
        Paginator::useBootstrap();

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

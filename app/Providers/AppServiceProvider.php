<?php

namespace App\Providers;

use App\Helpers\StorageHelper;
use App\Services\NomorDokumenService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;

use Illuminate\Support\ServiceProvider;

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

        // Hitung total & penggunaan storage di disk 'public'
        $used = StorageHelper::getFolderSize(); // tanpa argumen, default ke public
        $total = 1000 * 1024 * 1024 * 1024; //  total kapasitas: 800 GB
        // $total = 200 * 1024 * 1024; // total kapasitas: 200 MB
        $percentage = ($used / $total) * 100;

        // Bagikan ke semua view
        View::share([
            'used' => StorageHelper::formatBytes($used),
            'total' => StorageHelper::formatBytes($total),
            'percentage' => round($percentage, 2),
        ]);
    }
}

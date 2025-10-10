<?php

namespace App\Providers;

use App\Helpers\StorageHelper;
use Illuminate\Support\Facades\View;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Hitung total & penggunaan storage
        $used = StorageHelper::getFolderSize('storage'); // ganti 'storage' dengan path yang sesuai
        $total = 800 * 1024 * 1024 * 1024; // contoh total 800GB
        $percentage = ($used / $total) * 100;

        // Bagikan ke semua view
        View::share([
            'used' => StorageHelper::formatBytes($used),
            'total' => StorageHelper::formatBytes($total),
            'percentage' => round($percentage, 2),
        ]);
    }
}

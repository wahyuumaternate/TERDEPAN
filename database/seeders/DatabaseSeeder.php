<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Penugasan\Database\Seeders\PenugasanDatabaseSeeder;
use Modules\TerminalData\Database\Seeders\TerminalDataDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Aman dijalankan di production: hanya data master (jabatan, bidang, sub bidang),
     * satu akun ADMIN, role/permission, dan folder bidang. Data pegawai contoh & sample
     * penugasan (khusus dev/testing) hanya ikut jalan di luar environment production.
     */
    public function run(): void
    {
        $this->call([
            MasterJabatanSeeder::class,
            MasterBidangSeeder::class,
            MasterPegawaiSeeder::class,
            RolePermissionSeeder::class,
            TerminalDataDatabaseSeeder::class,
            // PerjanjianKinerjaDatabaseSeeder::class, // Modul PerjanjianKinerja nonaktif sementara (lihat modules_statuses.json)
        ]);

        // if (! app()->isProduction()) {
        //     $this->call([
        //         DataPegawaiSeeder::class,
        //         PenugasanDatabaseSeeder::class,
        //     ]);
        // }
    }
}

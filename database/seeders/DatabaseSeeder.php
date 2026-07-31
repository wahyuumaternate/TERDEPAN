<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Penugasan\Database\Seeders\PenugasanDatabaseSeeder;
use Modules\PerjanjianKinerja\Database\Seeders\PerjanjianKinerjaDatabaseSeeder;
use Modules\TerminalData\Database\Seeders\TerminalDataDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MasterJabatanSeeder::class,
            MasterBidangSeeder::class,
            MasterPegawaiSeeder::class,
            RolePermissionSeeder::class,
            // PegawaiWithAtasanSeeder::class,
            // PegawaiSeeder::class,
            // TestPegawaiSeeder::class,
            DataPegawaiSeeder::class,

            TerminalDataDatabaseSeeder::class,
            // PerjanjianKinerjaDatabaseSeeder::class, // Modul PerjanjianKinerja nonaktif sementara (lihat modules_statuses.json)
            PenugasanDatabaseSeeder::class,
        ]);
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}

<?php

namespace Modules\Dokumen\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Dokumen\Database\Seeders\DocJenisSeeder;

class DokumenDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            // KategoriDokumenSeeder::class,
            // DocJenisSeeder::class,
            DocFolderSeeder::class, // Seed folder hierarchy
        ]);
    }
}

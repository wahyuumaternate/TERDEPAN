<?php

namespace Modules\TerminalData\Database\Seeders;

use Illuminate\Database\Seeder;

class TerminalDataDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            // TdPermissionSeeder::class,
            TdFolderSeeder::class,
        ]);
    }
}

<?php

namespace Modules\PerjanjianKinerja\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class PerjanjianKinerjaDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Model::unguard();

        $this->call([
            PerjanjianKinerjaSeeder::class,
        ]);
    }
}

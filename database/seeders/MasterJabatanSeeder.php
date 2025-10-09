<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterJabatan;

class MasterJabatanSeeder extends Seeder
{
    public function run()
    {
        MasterJabatan::insert([
            [
                'kode' => 'KABAN',
                'nama' => 'Kepala Bappeda',
                'level' => 1,
                'is_struktural' => true,
                'bebas_nilai_kinerja' => false,
                'is_active' => true,
            ],
            [
                'kode' => 'SEKBAN',
                'nama' => 'Sekretaris Bappeda',
                'level' => 2,
                'is_struktural' => true,
                'bebas_nilai_kinerja' => false,
                'is_active' => true,
            ],
            [
                'kode' => 'KABID',
                'nama' => 'Kepala Bidang',
                'level' => 3,
                'is_struktural' => true,
                'bebas_nilai_kinerja' => false,
                'is_active' => true,
            ],
            [
                'kode' => 'JAFUNG',
                'nama' => 'Jafung/Pelaksana',
                'level' => 4,
                'is_struktural' => false,
                'bebas_nilai_kinerja' => false,
                'is_active' => true,
            ],
            [
                'kode' => 'TT',
                'nama' => 'Tenaga Teknis',
                'level' => 5,
                'is_struktural' => false,
                'bebas_nilai_kinerja' => true,
                'is_active' => true,
            ],
        ]);
    }
}

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
                'kode' => 'KASUBAG',
                'nama' => 'Kepala Sub Bagian',
                'level' => 3,
                'is_struktural' => true,
                'bebas_nilai_kinerja' => false,
                'is_active' => true,
            ],
            [
                'kode' => 'JAFUNG',
                'nama' => 'Pejabat Fungsional',
                'level' => 4,
                'is_struktural' => false,
                'bebas_nilai_kinerja' => false,
                'is_active' => true,
            ],
            [
                'kode' => 'PEL',
                'nama' => 'Pelaksana',
                'level' => 5,
                'is_struktural' => false,
                'bebas_nilai_kinerja' => false,
                'is_active' => true,
            ],
            [
                'kode' => 'GATEK',
                'nama' => 'Tenaga Teknis',
                'level' => 6,
                'is_struktural' => false,
                'bebas_nilai_kinerja' => true,
                'is_active' => true,
            ],
        ]);
    }
}

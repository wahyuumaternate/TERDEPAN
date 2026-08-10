<?php

namespace Database\Seeders;

use App\Models\MasterJabatan;
use Illuminate\Database\Seeder;

class MasterJabatanSeeder extends Seeder
{
    /**
     * Idempotent (firstOrCreate per kode) — aman dijalankan ulang di production tanpa
     * error unique constraint, mis. saat db:seed ikut dipanggil ulang di pipeline deploy.
     */
    public function run()
    {
        $daftarJabatan = [
            [
                'kode' => 'ADMIN',
                'nama' => 'Admin Utama',
                'level' => 1,
                'is_struktural' => false,
                'bebas_nilai_kinerja' => true,
                'is_active' => true,
            ],
            [
                'kode' => 'KABAN',
                'nama' => 'Kepala Bappeda',
                'level' => 1,
                'is_struktural' => true,
                'bebas_nilai_kinerja' => true,
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
                'kode' => 'PELAKSANA',
                'nama' => 'Pelaksana',
                'level' => 5,
                'is_struktural' => true,
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
        ];

        foreach ($daftarJabatan as $jabatan) {
            MasterJabatan::firstOrCreate(['kode' => $jabatan['kode']], $jabatan);
        }
    }
}

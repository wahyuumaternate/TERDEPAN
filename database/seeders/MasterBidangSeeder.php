<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterBidang;
use App\Models\MasterSubBidang;

class MasterBidangSeeder extends Seeder
{
    public function run()
    {
        MasterBidang::insert([
            [
                'kode' => 'BAPPEDA',
                'nama' => 'Bappeda',
                'warna' => '#FFFFFF',
                'is_active' => false,
            ],
            [
                'kode' => 'SEKRETARIAT',
                'nama' => 'Sekretariat',
                'warna' => '#64B5F6',
                'is_active' => true,
            ],
            [
                'kode' => 'EKONOMI',
                'nama' => 'Bidang Ekonomi dan Perdagangan',
                'warna' => '#81C784',
                'is_active' => true,
            ],
            [
                'kode' => 'IPW',
                'nama' => 'Bidang Infrastruktur dan Pembangunan Wilayah',
                'warna' => '#FFB74D',
                'is_active' => true,
            ],
            [
                'kode' => 'SOSBUD',
                'nama' => 'Bidang Pemerintahan dan Sosial Budaya',
                'warna' => '#E57373',
                'is_active' => true,
            ],
            [
                'kode' => 'PERAN',
                'nama' => 'Bidang Pengendalian, Evaluasi dan Pelaporan',
                'warna' => '#BA68C8',
                'is_active' => true,
            ],
        ]);

        MasterSubBidang::insert([
            [
                'bidang_id' => 2,
                'nama' => 'Sub Bagian Umum dan Kepegawaian',
            ],
            [
                'bidang_id' => 2,
                'nama' => 'Sub Bagian Perencanaan dan Program',
            ],
            [
                'bidang_id' => 2,
                'nama' => 'Sub Bagian Keuangan',
            ],
        ]);
    }
}

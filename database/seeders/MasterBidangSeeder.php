<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterBidang;

class MasterBidangSeeder extends Seeder
{
    public function run()
    {
        MasterBidang::insert([
            [
                'kode' => 'SEKRETARIAT',
                'nama' => 'Sekretariat',
                'deskripsi' => 'Bidang administrasi dan umum',
                'warna' => '#64B5F6',
                'is_active' => true,
            ],
            [
                'kode' => 'EKONOMI',
                'nama' => 'Bidang Ekonomi dan Perdagangan',
                'deskripsi' => 'Bidang Ekonomi dan Perdagangan',
                'warna' => '#81C784',
                'is_active' => true,
            ],
            [
                'kode' => 'IPW',
                'nama' => 'Bidang Infrastruktur dan Pembangunan Wilayah',
                'deskripsi' => 'Bidang Infrastruktur dan Pembangunan Wilayah',
                'warna' => '#FFB74D',
                'is_active' => true,
            ],
            [
                'kode' => 'SOSBUD',
                'nama' => 'Bidang Pemerintahan dan Sosial Budaya',
                'deskripsi' => 'Bidang Pemerintahan dan Sosial Budaya',
                'warna' => '#E57373',
                'is_active' => true,
            ],
            [
                'kode' => 'PERAN',
                'nama' => 'Bidang Pengendalian, Evaluasi dan Pelaporan',
                'deskripsi' => 'Bidang Pengendalian, Evaluasi dan Pelaporan',
                'warna' => '#BA68C8',
                'is_active' => true,
            ],
        ]);
    }
}

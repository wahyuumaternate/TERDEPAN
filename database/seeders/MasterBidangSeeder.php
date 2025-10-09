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
                'warna' => '#2196F3',
                'is_active' => true,
            ],
            [
                'kode' => 'EKONOMI',
                'nama' => 'Bidang Ekonomi dan Perdagangan',
                'deskripsi' => 'Bidang Ekonomi dan Perdagangan',
                'warna' => '#4CAF50',
                'is_active' => true,
            ],
            [
                'kode' => 'IPW',
                'nama' => 'Bidang Infrastruktur dan Pembangunan Wilayah',
                'deskripsi' => 'Bidang Infrastruktur dan Pembangunan Wilayah',
                'warna' => '#FFC107',
                'is_active' => true,
            ],
            [
                'kode' => 'SOSBUD',
                'nama' => 'Bidang Pemerintahan dan Sosial Budaya',
                'deskripsi' => 'Bidang Pemerintahan dan Sosial Budaya',
                'warna' => '#F44336',
                'is_active' => true,
            ],
            [
                'kode' => 'PERAN',
                'nama' => 'Bidang Pengendalian, Evaluasi dan Pelaporan',
                'deskripsi' => 'Bidang Pengendalian, Evaluasi dan Pelaporan',
                'warna' => '#FFF336',
                'is_active' => true,
            ],
        ]);
    }
}

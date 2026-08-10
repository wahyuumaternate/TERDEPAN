<?php

namespace Database\Seeders;

use App\Models\MasterBidang;
use App\Models\MasterSubBidang;
use Illuminate\Database\Seeder;

class MasterBidangSeeder extends Seeder
{
    /**
     * Idempotent (firstOrCreate per kode) — aman dijalankan ulang di production tanpa
     * error unique constraint, mis. saat db:seed ikut dipanggil ulang di pipeline deploy.
     */
    public function run()
    {
        $daftarBidang = [
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
        ];

        foreach ($daftarBidang as $bidang) {
            MasterBidang::firstOrCreate(['kode' => $bidang['kode']], $bidang);
        }

        $sekretariat = MasterBidang::where('kode', 'SEKRETARIAT')->first();

        $daftarSubBidang = [
            'Sub Bagian Umum dan Kepegawaian',
            'Sub Bagian Perencanaan dan Program',
            'Sub Bagian Keuangan',
        ];

        foreach ($daftarSubBidang as $nama) {
            MasterSubBidang::firstOrCreate(['bidang_id' => $sekretariat->id, 'nama' => $nama]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterPegawai;

class MasterPegawaiSeeder extends Seeder
{
    public function run()
    {
        MasterPegawai::create([
            'nomor_identitas' => '197001011990011001',
            'tipe_identitas' => 'NIP',
            'nama' => 'Admin Utama',
            'jabatan_id' => 1, // pastikan id jabatan 1 ada
            'bidang_id' => 1, // pastikan id bidang 1 ada
            'jenis_kelamin' => 'L',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password123'),
            'status_kepegawaian' => 'Kontrak',
        ]);
    }
}

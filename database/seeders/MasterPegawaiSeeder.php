<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterPegawai;

class MasterPegawaiSeeder extends Seeder
{
    public function run()
    {
        MasterPegawai::create([
            'nomor_identitas' => 'ADMIN',
            'tipe_identitas' => 'ID',
            'nama' => 'Admin Sistem',
            'jabatan_id' => 1, // pastikan id jabatan 1 ada
            'bidang_id' => 1, // pastikan id bidang 1 ada
            'jenis_kelamin' => 'L',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'status_kepegawaian' => 'Kontrak',
        ]);
    }
}

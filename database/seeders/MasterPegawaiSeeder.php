<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class MasterPegawaiSeeder extends Seeder
{
    public function run()
    {
        $user = User::create([
            'nama' => 'Admin Sistem',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
        ]);

        $user->profile()->create([
            'nomor_identitas' => 'ADMIN',
            'tipe_identitas' => 'ID',
            'jabatan_id' => 1, // pastikan id jabatan 1 ada
            'bidang_id' => 1, // pastikan id bidang 1 ada
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'Kontrak',
            'status_aktif' => 'Aktif',
        ]);
    }
}

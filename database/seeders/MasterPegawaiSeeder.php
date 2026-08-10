<?php

namespace Database\Seeders;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Database\Seeder;

class MasterPegawaiSeeder extends Seeder
{
    /**
     * Buat akun ADMIN default. Idempotent (aman dijalankan ulang di production tanpa
     * bikin duplikat/error unique constraint) — dan wajib ganti password saat login
     * pertama karena password default well-known ('password').
     */
    public function run()
    {
        if (User::where('email', 'admin@gmail.com')->exists()) {
            $this->command?->info('Akun admin sudah ada, skip seeding.');

            return;
        }

        $user = User::create([
            'nama' => 'Admin Sistem',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'must_change_password' => true,
        ]);

        $user->profile()->create([
            'nomor_identitas' => 'ADMIN',
            'tipe_identitas' => 'ID',
            'jabatan_id' => MasterJabatan::where('kode', 'ADMIN')->value('id'),
            'bidang_id' => MasterBidang::where('kode', 'BAPPEDA')->value('id'),
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'Kontrak',
            'status_aktif' => 'Aktif',
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MasterPegawai;
use Illuminate\Support\Facades\Hash;


class TestPegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a simple hierarchy of test pegawai as requested:
        // 1 KABAN, 1 SEKBAN, 1 JAFUNG, 3 KASUBAG, 3 PELAKSANA, 3 GATEK

        $baseIdent = 200000000000000000; // will increment for unique nomor_identitas

        // KABAN (jabatan_id = 2)
        $kaban = MasterPegawai::create([
            'nomor_identitas' => (string)($baseIdent + 1),
            'tipe_identitas' => 'NIP',
            'nama' => 'Kaban Test',
            'jabatan_id' => 2,
            'bidang_id' => 1,
            'email' => 'kaban@test.local',
            'password' => Hash::make('password'),
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'tanggal_masuk' => now()->subYears(12)->toDateString(),
        ]);

        // SEKBAN (jabatan_id = 3), direct report to KABAN
        $sekban = MasterPegawai::create([
            'nomor_identitas' => (string)($baseIdent + 2),
            'tipe_identitas' => 'NIP',
            'nama' => 'Sekban Test',
            'jabatan_id' => 3,
            'bidang_id' => 2,
            'email' => 'sekban@test.local',
            'password' => Hash::make('password'),
            'jenis_kelamin' => 'P',
            'status_kepegawaian' => 'PNS',
            'tanggal_masuk' => now()->subYears(10)->toDateString(),
            'atasan_langsung_id' => $kaban->id,
        ]);

        // 1 JAFUNG (jabatan_id = 6)
        $jafung = MasterPegawai::create([
            'nomor_identitas' => (string)($baseIdent + 3),
            'tipe_identitas' => 'NIP',
            'nama' => 'Jafung Test',
            'jabatan_id' => 6,
            'bidang_id' => 2,
            'email' => 'jafung@test.local',
            'password' => Hash::make('password'),
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'tanggal_masuk' => now()->subYears(5)->toDateString(),
            'atasan_langsung_id' => $sekban->id,
        ]);

        // 3 KASUBAG (jabatan_id = 5) - report to SEKBAN
        $kasubags = [];
        for ($i = 1; $i <= 3; $i++) {
            $kasubags[$i] = MasterPegawai::create([
                'nomor_identitas' => (string)($baseIdent + 3 + $i),
                'tipe_identitas' => 'NIP',
                'nama' => "Kasubag Test $i",
                'jabatan_id' => 5,
                'bidang_id' => 2,
                'sub_bidang_id' => $i, // assign to different sub bidang
                'email' => "kasubag{$i}@test.local",
                'password' => Hash::make('password'),
                'jenis_kelamin' => $i % 2 ? 'P' : 'L',
                'status_kepegawaian' => 'PNS',
                'tanggal_masuk' => now()->subYears(3)->toDateString(),
                'atasan_langsung_id' => $sekban->id,
            ]);
        }

        // 3 PELAKSANA (jabatan_id = 7) - assign under first Kasubag
        $pelaksanas = [];
        for ($i = 1; $i <= 3; $i++) {
            $pelaksanas[$i] = MasterPegawai::create([
                'nomor_identitas' => (string)($baseIdent + 6 + $i),
                'tipe_identitas' => 'NIP',
                'nama' => "Pelaksana Test $i",
                'jabatan_id' => 7,
                'bidang_id' => 2,
                'sub_bidang_id' => $i, // assign to different sub bidang
                'email' => "pelaksana{$i}@test.local",
                'password' => Hash::make('password'),
                'jenis_kelamin' => $i % 2 ? 'L' : 'P',
                'status_kepegawaian' => 'PNS',
                'tanggal_masuk' => now()->subYears(1)->toDateString(),
                'atasan_langsung_id' => $kasubags[$i]->id,
            ]);
        }

        // 3 TENAGA TEKNIS (GATEK, jabatan_id = 8) - assign under first Pelaksana
        for ($i = 1; $i <= 3; $i++) {
            MasterPegawai::create([
                'nomor_identitas' => (string)($baseIdent + 9 + $i),
                'tipe_identitas' => 'NIP',
                'nama' => "Gatek Test $i",
                'jabatan_id' => 8,
                'bidang_id' => 2,
                'sub_bidang_id' => $i, // assign to different sub bidang
                'email' => "gatek{$i}@test.local",
                'password' => Hash::make('password'),
                'jenis_kelamin' => $i % 2 ? 'L' : 'P',
                'status_kepegawaian' => 'PNS',
                'tanggal_masuk' => now()->subMonths(6)->toDateString(),
                'atasan_langsung_id' => $kasubags[$i]->id,
            ]);
        }
    }
}

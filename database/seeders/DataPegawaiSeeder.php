<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\PegawaiCsvImporter;
use Illuminate\Database\Seeder;

class DataPegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Parsing CSV & mapping jabatan/bidang/atasan didelegasikan ke PegawaiCsvImporter
     * (app/Services/PegawaiCsvImporter.php) — service yang sama dipakai fitur import
     * di halaman Kelola Data Pegawai, supaya logic-nya tidak dobel ditulis.
     */
    public function run(): void
    {
        $this->command->info('🔄 Memulai import data pegawai dari CSV files...');

        $basePath = base_path('FILE KEPERLUAN RANCANGAN');
        $csvFiles = [
            $basePath.'/TERDEPAN Data Pegawai 1.csv',
            $basePath.'/TERDEPAN Data Pegawai 2.csv',
            $basePath.'/TERDEPAN Data Pegawai 3.csv',
        ];

        $importer = new PegawaiCsvImporter;

        $allRows = [];
        foreach ($csvFiles as $file) {
            if (! file_exists($file)) {
                $this->command->warn("⚠️  File not found: {$file}");

                continue;
            }

            $allRows = array_merge($allRows, $importer->parseCsv($file));
        }

        if (empty($allRows)) {
            $this->command->error('❌ No data found in CSV files!');

            return;
        }

        $hasil = $importer->import($allRows);

        foreach ($hasil['berhasil'] as $pegawai) {
            $this->command->info("✓ {$pegawai->nama}");
        }

        foreach ($hasil['dilewati'] as $item) {
            $this->command->warn("⚠️  Baris {$item['baris']} ({$item['nama']}) dilewati: {$item['alasan']}");
        }

        $this->displayPegawaiTable($hasil['berhasil']);
    }

    /**
     * Display pegawai table after seeding
     *
     * @param  array<int, User>  $pegawaiList
     */
    private function displayPegawaiTable(array $pegawaiList): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════════════════════════════════');
        $this->command->info('                        DAFTAR USER YANG BERHASIL DICREATE                               ');
        $this->command->info('═══════════════════════════════════════════════════════════════════════════════════════');
        $this->command->info('');

        $pegawaiIds = array_map(fn ($p) => $p->id, $pegawaiList);
        $pegawaiWithRelations = User::with(['profile.jabatan', 'profile.bidang', 'profile.atasanLangsung'])
            ->whereIn('id', $pegawaiIds)
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        $headers = ['No', 'Nama', 'Jabatan', 'Bidang', 'NIP/ID', 'Email', 'Atasan'];
        $rows = [];

        foreach ($pegawaiList as $index => $pegawai) {
            $pegawaiLoaded = $pegawaiWithRelations->get($pegawai->id);

            $jabatan = $pegawaiLoaded->profile?->jabatan->nama ?? '-';
            $bidang = $pegawaiLoaded->profile?->bidang->nama ?? '-';
            $atasan = $pegawaiLoaded->profile?->atasanLangsung->nama ?? '-';

            $rows[] = [
                $index + 1,
                $pegawaiLoaded->nama,
                $jabatan,
                $bidang,
                $pegawaiLoaded->profile?->nomor_identitas,
                $pegawaiLoaded->email,
                $atasan,
            ];
        }

        $this->command->table($headers, $rows);

        $totalCount = count($pegawaiList);
        $this->command->info('');
        $this->command->info("✅ Total {$totalCount} user berhasil dibuat!");
        $this->command->info('ℹ️  Semua user menggunakan password default: password');
        $this->command->info('');
    }
}

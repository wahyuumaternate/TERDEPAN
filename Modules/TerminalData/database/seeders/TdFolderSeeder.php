<?php

namespace Modules\TerminalData\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TdFolderSeeder extends Seeder
{
    public function run(): void
    {
        // Skip jika data sudah ada
        if (DB::table('td_folders')->count() > 0) {
            $this->command->info('Folder hierarchy sudah ada, skip seeding...');
            return;
        }

        // Get all bidang
        $bidangList = DB::table('master_bidang')->get();

        // Get admin/system user (ID=1, atau bisa diganti sesuai kebutuhan)
        $systemUserId = 1;

        // Jenis folder Level 2 yang akan dibuat untuk setiap bidang
        $jenisFolder = [
            'Eviden Kinerja',
            'Bahan Tayang',
            'Laporan',
            'Surat',
            'Data Spasial',
            'Perjanjian',
            'Arsip Lainnya'
        ];

        $now = Carbon::now();

        foreach ($bidangList as $bidang) {
            // Level 1: Folder Bidang
            $bidangFolderId = (string) \Illuminate\Support\Str::uuid();
            $bidangPath = '/' . $this->slugify($bidang->nama);

            DB::table('td_folders')->insert([
                'id' => $bidangFolderId,
                'parent_id' => null,
                'bidang_id' => $bidang->id,
                'name' => $bidang->nama,
                'path' => $bidangPath,
                'level' => 0,
                'is_system' => false,
                'total_files' => 0,
                'total_subfolders' => 0,
                'total_size' => 0,
                'created_by' => $systemUserId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Level 2: Folder Jenis untuk setiap bidang
            foreach ($jenisFolder as $jenis) {
                $jenisFolderId = (string) \Illuminate\Support\Str::uuid();
                $jenisPath = $bidangPath . '/' . $this->slugify($jenis);

                DB::table('td_folders')->insert([
                    'id' => $jenisFolderId,
                    'parent_id' => $bidangFolderId,
                    'bidang_id' => $bidang->id,
                    'name' => $jenis,
                    'path' => $jenisPath,
                    'level' => 1,
                    'is_system' => false,
                    'total_files' => 0,
                    'total_subfolders' => 0,
                    'total_size' => 0,
                    'created_by' => $systemUserId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Level 3: Khusus untuk "Eviden Kinerja", buat subfolder per pegawai
                if ($jenis === 'Eviden Kinerja') {
                    // Get pegawai dari bidang ini
                    $pegawaiList = DB::table('master_pegawai')
                        ->where('bidang_id', $bidang->id)
                        ->get();

                    foreach ($pegawaiList as $pegawai) {
                        $pegawaiFolderId = (string) \Illuminate\Support\Str::uuid();
                        $pegawaiPath = $jenisPath . '/' . $this->slugify($pegawai->nama);

                        DB::table('td_folders')->insert([
                            'id' => $pegawaiFolderId,
                            'parent_id' => $jenisFolderId,
                            'bidang_id' => $bidang->id,
                            'name' => $pegawai->nama . ' (' . $pegawai->tipe_identitas . ': ' . $pegawai->nomor_identitas . ')',
                            'path' => $pegawaiPath,
                            'level' => 2,
                            'is_system' => true,
                            'total_files' => 0,
                            'total_subfolders' => 0,
                            'total_size' => 0,
                            'created_by' => $systemUserId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Convert string to URL-safe slug
     */
    private function slugify(string $text): string
    {
        // Replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // Transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // Trim
        $text = trim($text, '-');

        // Remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // Lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}

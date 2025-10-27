<?php

namespace Modules\Dokumen\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DocFolderSeeder extends Seeder
{
    public function run(): void
    {
        // Skip jika data sudah ada
        if (DB::table('doc_folder')->count() > 0) {
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
        $folderId = 1;

        foreach ($bidangList as $bidang) {
            // Level 1: Folder Bidang
            $bidangFolderId = $folderId++;
            $bidangPath = '/' . $this->slugify($bidang->nama);

            DB::table('doc_folder')->insert([
                'id' => $bidangFolderId,
                'parent_id' => null,
                'bidang_id' => $bidang->id,
                'nama' => $bidang->nama,
                'path' => $bidangPath,
                'level' => 1,
                'is_auto' => false,
                'total_files' => 0,
                'created_by' => $systemUserId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Level 2: Folder Jenis untuk setiap bidang
            foreach ($jenisFolder as $jenis) {
                $jenisFolderId = $folderId++;
                $jenisPath = $bidangPath . '/' . $this->slugify($jenis);

                DB::table('doc_folder')->insert([
                    'id' => $jenisFolderId,
                    'parent_id' => $bidangFolderId,
                    'bidang_id' => $bidang->id,
                    'nama' => $jenis,
                    'path' => $jenisPath,
                    'level' => 2,
                    'is_auto' => false,
                    'total_files' => 0,
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
                        $pegawaiFolderId = $folderId++;
                        $pegawaiPath = $jenisPath . '/' . $this->slugify($pegawai->nama);

                        DB::table('doc_folder')->insert([
                            'id' => $pegawaiFolderId,
                            'parent_id' => $jenisFolderId,
                            'bidang_id' => $bidang->id,
                            'nama' => $pegawai->nama . ' (' . $pegawai->tipe_identitas . ': ' . $pegawai->nomor_identitas . ')',
                            'path' => $pegawaiPath,
                            'level' => 3,
                            'is_auto' => true, // Auto-generated untuk pegawai
                            'total_files' => 0,
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

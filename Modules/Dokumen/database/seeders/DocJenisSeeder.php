<?php

namespace Modules\Dokumen\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Dokumen\Models\JenisDokumen;
use Carbon\Carbon;

class DocJenisSeeder extends Seeder
{
    public function run(): void
    {
        JenisDokumen::insert([
            [
                'id' => 1,
                'kategori_id' => 1,
                'kode' => 'SM',
                'nama' => 'Surat Masuk',
                'folder_pattern' => '/{bidang}/{jenis}/{year}/{month}/',
                'nomor_format' => 'SM/BAPPEDA/{bidang}/{year}/{seq}',
                'allowed_ext' => 'pdf,docx,xlsx',
                'max_size_mb' => 10,
                'perlu_nomor' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'kategori_id' => 1,
                'kode' => 'SK',
                'nama' => 'Surat Keluar',
                'folder_pattern' => '/{bidang}/{jenis}/{year}/{month}/',
                'nomor_format' => 'SK/BAPPEDA/{bidang}/{year}/{seq}',
                'allowed_ext' => 'pdf,docx,xlsx',
                'max_size_mb' => 10,
                'perlu_nomor' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'kategori_id' => 2,
                'kode' => 'SHP',
                'nama' => 'Shapefile',
                'folder_pattern' => '/{bidang}/{jenis}/{year}/{month}/',
                'nomor_format' => null,
                'allowed_ext' => 'zip,rar',
                'max_size_mb' => 50,
                'perlu_nomor' => false,
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'kategori_id' => 3,
                'kode' => 'LAP',
                'nama' => 'Laporan',
                'folder_pattern' => '/{bidang}/{jenis}/{year}/{month}/',
                'nomor_format' => null,
                'allowed_ext' => 'pdf,docx,xlsx',
                'max_size_mb' => 20,
                'perlu_nomor' => false,
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'kategori_id' => 4,
                'kode' => 'PK',
                'nama' => 'Perjanjian Kinerja',
                'folder_pattern' => '/{bidang}/{jenis}/{year}/{month}/',
                'nomor_format' => 'PK/BAPPEDA/{bidang}/{year}/{seq}',
                'allowed_ext' => 'pdf,docx',
                'max_size_mb' => 15,
                'perlu_nomor' => true,
                'created_at' => Carbon::now(),
            ],
        ]);
    }
}

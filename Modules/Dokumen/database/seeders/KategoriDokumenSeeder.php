<?php

namespace Modules\Dokumen\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriDokumenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('doc_kategori')->insert([
            ['id' => 2, 'nama' => 'Surat', 'created_at' => Carbon::now()],
            ['id' => 3, 'nama' => 'Data Spasial', 'created_at' => Carbon::now()],
            ['id' => 4, 'nama' => 'Laporan', 'created_at' => Carbon::now()],
            ['id' => 5, 'nama' => 'Perjanjian', 'created_at' => Carbon::now()],
        ]);
    }
}

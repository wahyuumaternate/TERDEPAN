<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Aturan E4: bobot boleh diisi di awal atau di akhir (sebelum realisasi diberikan) —
     * kolom ini sebelumnya NOT NULL sehingga menutup opsi "diisi di akhir" sejak awal.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE knj_penugasan ALTER COLUMN bobot_persen DROP NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('knj_penugasan')->whereNull('bobot_persen')->update(['bobot_persen' => 0]);
        DB::statement('ALTER TABLE knj_penugasan ALTER COLUMN bobot_persen SET NOT NULL');
    }
};

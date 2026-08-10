<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah 'CPNS' (Calon PNS, status sebelum diangkat penuh jadi PNS) ke daftar nilai
     * status_kepegawaian yang diizinkan — ditemukan saat import data DUK asli, yang
     * membedakan pegawai CPNS dari PNS penuh. Kolom ini bukan native Postgres ENUM,
     * melainkan varchar + CHECK constraint (emulasi enum Laravel), jadi diubah lewat
     * drop+add constraint, bukan ALTER TYPE.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE user_profiles DROP CONSTRAINT user_profiles_status_kepegawaian_check');
        DB::statement("ALTER TABLE user_profiles ADD CONSTRAINT user_profiles_status_kepegawaian_check CHECK (status_kepegawaian IN ('PNS', 'CPNS', 'PPPK', 'Kontrak'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE user_profiles DROP CONSTRAINT user_profiles_status_kepegawaian_check');
        DB::statement("ALTER TABLE user_profiles ADD CONSTRAINT user_profiles_status_kepegawaian_check CHECK (status_kepegawaian IN ('PNS', 'PPPK', 'Kontrak'))");
    }
};

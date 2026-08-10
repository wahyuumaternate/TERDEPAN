<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Foto profil pegawai selalu di disk 'public' (harus bisa diakses langsung lewat
     * <img src> tanpa lewat route terautentikasi) — beda dari dokumen TerminalData yang
     * privat by default (docs/plan/09-audit-storage.md).
     */
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('disk')->nullable()->default('public')->after('foto_profile_path');
        });

        DB::table('user_profiles')->whereNotNull('foto_profile_path')->update(['disk' => 'public']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn('disk');
        });
    }
};

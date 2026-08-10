<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('knj_penugasan', function (Blueprint $table) {
            // Menandai kapan tugas ditolak (status jadi 'ditolak') — dipakai job terjadwal untuk
            // menghitung masa tenggang 24 jam sebelum benar-benar dihapus (soft delete), dan untuk
            // menentukan apakah penolakan masih bisa dibatalkan lewat aksi "Batalkan Penolakan".
            $table->timestamp('ditolak_pada')->nullable()->after('alasan_reject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('knj_penugasan', function (Blueprint $table) {
            $table->dropColumn('ditolak_pada');
        });
    }
};

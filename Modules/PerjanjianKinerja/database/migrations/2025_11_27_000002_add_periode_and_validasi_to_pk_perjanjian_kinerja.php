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
        Schema::table('pk_perjanjian_kinerja', function (Blueprint $table) {
            $table->foreignId('periode_id')->nullable()->after('template_id')->constrained('pk_periode')->onDelete('set null');
            $table->foreignId('divalidasi_oleh')->nullable()->after('status_dokumen')->constrained('users')->onDelete('set null');
            $table->timestamp('divalidasi_pada')->nullable()->after('divalidasi_oleh');
            $table->text('catatan_validasi')->nullable()->after('divalidasi_pada');
            $table->enum('status_validasi', ['Menunggu', 'Disetujui', 'Ditolak', 'Revisi'])->default('Menunggu')->after('catatan_validasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pk_perjanjian_kinerja', function (Blueprint $table) {
            $table->dropForeign(['periode_id']);
            $table->dropForeign(['divalidasi_oleh']);
            $table->dropColumn([
                'periode_id',
                'divalidasi_oleh',
                'divalidasi_pada',
                'catatan_validasi',
                'status_validasi',
            ]);
        });
    }
};

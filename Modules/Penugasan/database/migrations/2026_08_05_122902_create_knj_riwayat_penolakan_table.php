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
        // Audit trail penolakan tugas (solo maupun grup kolektif) — dicatat SEBELUM cascade
        // status/hapus terjadi, supaya riwayatnya tetap ada walau record Penugasan yang
        // bersangkutan nanti benar-benar di-soft-delete oleh job terjadwal (bukan tabel
        // pivot ke knj_penugasan by design, karena record itu sendiri bisa hilang dari
        // query normal — snapshot id-nya disimpan sebagai JSON supaya tetap tertelusuri).
        Schema::create('knj_riwayat_penolakan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('grup_id')->nullable()->index();
            $table->json('penugasan_ids')->comment('Snapshot id penugasan (koordinator + anggota) yang terdampak');
            $table->foreignId('ditolak_oleh')->constrained('users')->restrictOnDelete();
            $table->text('alasan_reject');
            $table->timestamp('ditolak_pada');
            $table->timestamp('dibatalkan_pada')->nullable();
            $table->timestamp('dieksekusi_pada')->nullable()->comment('Kapan job terjadwal benar-benar menghapus record-nya');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_riwayat_penolakan');
    }
};

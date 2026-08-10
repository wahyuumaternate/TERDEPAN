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
        Schema::create('knj_perpanjangan_waktu', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('penugasan_id')->constrained('knj_penugasan')->cascadeOnDelete();
            $table->foreignId('pegawai_id')->constrained('users')->comment('Pemohon, sama dengan pegawai_id tugas');

            $table->date('deadline_lama');
            $table->date('deadline_diminta');
            $table->date('deadline_disetujui')->nullable();

            $table->text('alasan_pengajuan');
            $table->text('catatan_atasan')->nullable();

            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->foreignId('disetujui_oleh_id')->nullable()->constrained('users');

            $table->unsignedTinyInteger('ke_berapa');

            $table->timestamps();

            $table->index(['penugasan_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_perpanjangan_waktu');
    }
};

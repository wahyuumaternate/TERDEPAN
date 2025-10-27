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
        Schema::create('knj_tugas_tambahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('master_pegawai')->comment('Penerima tugas');
            $table->foreignId('pemberi_tugas_id')->constrained('master_pegawai')->comment('Atasan yang assign');
            $table->string('nama_tugas')->comment('Nama tugas');
            $table->text('deskripsi')->comment('Deskripsi tugas');
            $table->text('alasan_penugasan')->nullable()->comment('Kenapa pegawai ini yang ditugaskan');
            $table->date('tanggal_mulai')->comment('Tanggal mulai tugas');
            $table->date('deadline')->comment('Deadline tugas');

            // Field penilaian (ganti bobot_persen)
            $table->decimal('target_penilaian', 5, 2)->nullable()->comment('Target penilaian 0-100');
            $table->decimal('penilaian', 5, 2)->nullable()->comment('Penilaian aktual 0-100');
            $table->decimal('nilai_akhir', 5, 2)->nullable()->comment('Nilai akhir 0-100');
            $table->date('tanggal_penilaian')->nullable()->comment('Tanggal pemberian penilaian');

            $table->enum('status', ['pending', 'dikerjakan', 'validasi', 'revisi', 'selesai'])->default('pending');

            // Field validasi berjenjang
            $table->foreignId('validasi_oleh')->nullable()->constrained('master_pegawai')->comment('Yang melakukan validasi');
            $table->date('tanggal_validasi')->nullable()->comment('Tanggal validasi');
            $table->text('catatan_validasi')->nullable()->comment('Catatan validasi/revisi');

            // Dokumen lampiran
            $table->foreignId('dokumen_lampiran_id')->nullable()->constrained('doc_dokumen')->comment('Foreign key ke DOC_DOKUMEN');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pegawai_id', 'status']);
            $table->index('pemberi_tugas_id');
            $table->index('validasi_oleh');
            $table->index('deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_tugas_tambahan');
    }
};

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
        Schema::create('knj_penugasan', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relasi
            $table->foreignId('pegawai_id')->constrained('users')->comment('Penerima tugas');
            $table->foreignId('pemberi_tugas_id')->nullable()->constrained('users')
                ->comment('Atasan pemberi tugas. NULL = tugas mandiri/self-initiated');

            // Klasifikasi (label pelaporan, bukan penentu bobot)
            $table->enum('jenis', ['pokok', 'tambahan']);

            // Detail tugas
            $table->string('nama_tugas');
            $table->text('deskripsi');
            $table->text('alasan_penugasan')->nullable()
                ->comment('Kenapa pegawai ini yang ditugaskan, berlaku untuk tugas yang tidak mandiri');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->comment('Deadline tugas');

            // Target kuantitas (opsional, deskriptif)
            $table->decimal('target_value', 10, 2)->nullable();
            $table->string('satuan', 30)->nullable();

            // Penilaian: nilai_akhir = bobot_persen x realisasi_persen / 100
            $table->decimal('bobot_persen', 5, 2)->comment('Bobot kontribusi tugas ini terhadap total penilaian periode');
            $table->decimal('progress_persen', 5, 2)->default(0)->comment('Progress berjalan yang dilaporkan mandiri oleh pegawai');
            $table->decimal('realisasi_persen', 5, 2)->nullable()->comment('Capaian akhir 0-100 yang ditetapkan validator');
            $table->decimal('nilai_akhir', 5, 2)->nullable()->comment('Auto-calculated: bobot_persen x realisasi_persen / 100');

            // Status & alur validasi (seragam untuk semua jenis)
            $table->enum('status', ['pending', 'dikerjakan', 'validasi', 'revisi', 'selesai'])->default('pending');

            // Alur tugas mandiri (pemberi_tugas_id NULL)
            $table->enum('status_approval', ['pending', 'diterima', 'ditolak'])->nullable()
                ->comment('Hanya relevan kalau pemberi_tugas_id NULL');
            $table->text('alasan_reject')->nullable();

            // Alur validasi atasan
            $table->foreignId('validator_id')->nullable()->constrained('users');
            $table->enum('hasil_validasi', ['diterima', 'revisi', 'ditolak'])->nullable();
            $table->text('catatan_validasi')->nullable();
            $table->timestamp('validated_at')->nullable();

            $table->timestamp('diterima_at')->nullable()->comment('Waktu pegawai menerima penugasan');

            // File attachments via polymorphic relation ke td_files (attachable_*)
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pegawai_id', 'status']);
            $table->index(['jenis', 'status']);
            $table->index('pemberi_tugas_id');
            $table->index('validator_id');
            $table->index('tanggal_selesai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_penugasan');
    }
};

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
            $table->uuid('id')->primary();

            // Relasi
            $table->foreignId('pegawai_id')->constrained('master_pegawai');
            $table->foreignId('pemberi_tugas_id')->constrained('master_pegawai')
                ->comment('Wajib ada - always from supervisor');

            // Detail tugas
            $table->string('nama_tugas');
            $table->text('deskripsi');
            $table->text('alasan_penugasan')->nullable()
                ->comment('Kenapa pegawai ini yang ditugaskan');
            $table->date('tanggal_mulai')->comment('Tanggal mulai tugas');
            $table->date('tanggal_selesai')->comment('Deadline tugas');

            // Status 
            $table->enum('status', [
                'pending',      // Baru dibuat
                'dikerjakan',  // Sedang dikerjakan
                'validasi',    // menunggu di validasi
                'revisi',     // Perlu revisi
                'selesai'     // Selesai & divalidasi
            ])->default('pending');

            // Field validasi berjenjang
            $table->foreignId('validator_id')->nullable()->constrained('master_pegawai');
            $table->enum('hasil_validasi', ['diterima', 'revisi', 'ditolak'])->nullable();
            $table->text('catatan_validasi')->nullable();
            $table->tinyInteger('penilaian_kualitas')->nullable()
                ->comment('Score 1-5');
            $table->timestamp('validated_at')->nullable();

            // Field Penilaian
            $table->decimal('target_penilaian', 5, 2)->nullable()
                ->comment('Target score 0-100');
            $table->decimal('nilai_akhir', 5, 2)->nullable()
                ->comment('Final score 0-100, max 20% bonus');

            // File attachments via polymorphic relation to td_files (handled in td_files.attachable_*)
            // No direct foreign key needed - files will reference this table via polymorphic

            $table->timestamps();
            $table->softDeletes();

            $table->index(['pegawai_id', 'status']);
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
        Schema::dropIfExists('knj_tugas_tambahan');
    }
};

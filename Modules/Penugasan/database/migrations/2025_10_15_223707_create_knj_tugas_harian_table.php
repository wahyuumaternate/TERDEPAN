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
        Schema::create('knj_tugas_harian', function (Blueprint $table) {
            $table->uuid()->primary()->comment('Primary key UUID');

            // Relasi
            $table->foreignUuid('tugas_pokok_id')->constrained('knj_tugas_pokok')
                ->comment('Parent tugas pokok');
            $table->foreignId('pegawai_id')->constrained('master_pegawai');
            $table->foreignId('pemberi_tugas_id')->nullable()->constrained('master_pegawai')->comment('NULL jika self-initiated');

            // Khusus penugasan mandiri
            $table->boolean('is_mandiri')->default(false)
                ->comment('TRUE jika pegawai buat sendiri tanpa perintah atasan');
            $table->enum('status_approval', ['pending', 'diterima', 'ditolak'])->nullable()
                ->comment('Hanya untuk is_mandiri=true, NULL untuk tugas dari atasan');
            $table->text('alasan_reject')->nullable()
                ->comment('Alasan rejection untuk tugas mandiri');

            // Detail tugas
            $table->string('nama_tugas');
            $table->text('deskripsi');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->comment('Deadline tugas');

            // Target
            $table->decimal('target_value', 10, 2);
            $table->string('satuan', 30);

            // Status 
            $table->enum('status', [
                'pending',      // Baru dibuat
                'dikerjakan',  // Sedang dikerjakan
                'validasi',    // menunggu di validasi
                'revisi',     // Perlu revisi
                'selesai'     // Selesai & divalidasi
            ])->default('pending');

            // Field validasi berjenjang
            $table->foreignId('validator_id')->nullable()->constrained('master_pegawai')
                ->comment('Atasan yang validasi');
            $table->enum('hasil_validasi', ['diterima', 'revisi', 'ditolak'])->nullable();
            $table->text('catatan_validasi')->nullable();
            $table->tinyInteger('penilaian_kualitas')->nullable()
                ->comment('Score 1-5');
            $table->timestamp('validated_at')->nullable();

            // Field penilaian
            $table->decimal('target_penilaian', 5, 2)->nullable()
                ->comment('Target score 0-100');
            $table->decimal('nilai_akhir', 5, 2)->nullable()
                ->comment('Final score 0-100 from validator');

            // File attachments via polymorphic relation to td_files (handled in td_files.attachable_*)
            // No direct foreign key needed - files will reference this table via polymorphic

            $table->timestamps();
            $table->softDeletes();

            // Index untuk performa query
            $table->index(['pegawai_id', 'status']);
            $table->index(['tugas_pokok_id', 'status']);
            $table->index(['is_mandiri', 'status_approval']);
            $table->index('tanggal_selesai');
            $table->index('validator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_tugas_harian');
    }
};

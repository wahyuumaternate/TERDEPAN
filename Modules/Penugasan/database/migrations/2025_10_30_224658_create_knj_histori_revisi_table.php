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
        Schema::create('knj_histori_revisi', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Polymorphic relation (to tugas_harian, tugas_tambahan)
            // Note: tugas_pokok tidak bisa revisi (read-only from PK)
            $table->string('tipe_revisi')
                ->comment('TugasHarian, TugasTambahan');
            $table->foreignUuid('tipe_revisi_id');

            // Informasi Revisi
            $table->integer('revisi_ke')->default(1)->comment('Nomor revisi ke berapa');
            $table->timestamp('tanggal_revisi')->comment('Tanggal revisi dilakukan');
            $table->text('catatan_revisi')->comment('Catatan detail');

            // Deadline dan penalti
            $table->timestamp('deadline_revisi');
            $table->boolean('is_terlambat')->default(false);
            $table->decimal('penalty_nilai', 5, 2)->default(0);

            // Aktor
            $table->foreignId('direvisi_oleh')->constrained('users')->comment('Atasan yang minta revisi');
            $table->foreignId('pegawai_id')->nullable()->constrained('users')->comment('Pegawai yang merevisi');

            // Tanggal submit revisi
            $table->timestamp('submitted_at')->nullable()->comment('Waktu submit revisi');
            $table->enum('status', ['pending', 'dikirim', 'diterima'])->default('pending');

            // File attachments via polymorphic relation to td_files (handled in td_files.attachable_*)
            // No direct foreign key needed - files will reference this table via polymorphic

            $table->timestamps();

            $table->index(['pegawai_id', 'status']);
            $table->index(['tipe_revisi', 'tipe_revisi_id'], 'revisable_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_histori_revisi');
    }
};

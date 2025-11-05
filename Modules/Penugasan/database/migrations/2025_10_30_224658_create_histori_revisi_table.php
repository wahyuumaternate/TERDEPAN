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
        Schema::create('histori_revisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_harian_id')->nullable()->constrained('knj_tugas_harian')->comment('Link ke tugas harian');
            $table->foreignId('tugas_tambahan_id')->nullable()->constrained('knj_tugas_tambahan')->comment('Link ke tugas tambahan');
            $table->integer('revisi_ke')->default(1)->comment('Nomor revisi ke berapa');
            $table->timestamp('tanggal_revisi')->comment('Tanggal revisi dilakukan');
            $table->text('catatan_revisi')->comment('Alasan/catatan revisi');
            $table->foreignId('direvisi_oleh')->constrained('master_pegawai')->comment('Atasan yang melakukan revisi');

            // File attachments via polymorphic relation to td_files (handled in td_files.attachable_*)
            // No direct foreign key needed - files will reference this table via polymorphic

            $table->timestamps();

            $table->index(['tugas_harian_id', 'revisi_ke']);
            $table->index(['tugas_tambahan_id', 'revisi_ke']);
            $table->index('direvisi_oleh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histori_revisi');
    }
};

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
        Schema::create('knj_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_pokok_id')->nullable()->constrained('knj_tugas_pokok')->comment('Link ke KNJ_TUGAS_POKOK if applicable');
            $table->foreignId('tugas_harian_id')->nullable()->constrained('knj_tugas_harian')->comment('Link ke KNJ_TUGAS_HARIAN if applicable');
            $table->foreignId('tugas_tambahan_id')->nullable()->constrained('knj_tugas_tambahan')->comment('Link ke KNJ_TUGAS_TAMBAHAN if applicable');
            $table->foreignId('penugasan_mandiri_id')->nullable()->constrained('knj_penugasan_mandiri')->comment('Link ke KNJ_PENUGASAN_MANDIRI if applicable');
            $table->foreignId('pegawai_id')->constrained('master_pegawai')->comment('Pegawai yang update');
            $table->date('tanggal')->comment('Tanggal progress');
            $table->decimal('progress_persen', 5, 2)->comment('Progress hari ini cumulative 0-100%');
            $table->text('deskripsi_kegiatan')->comment('Apa yang dikerjakan hari ini');
            $table->text('kendala')->nullable()->comment('Kendala yang dihadapi optional');
            $table->foreignId('dokumen_bukti_id')->nullable()->constrained('doc_dokumen')->comment('Foreign key ke DOC_DOKUMEN untuk bukti');
            $table->timestamps();

            $table->index(['pegawai_id', 'tanggal']);
            $table->index('tugas_pokok_id');
            $table->index('tugas_harian_id');
            $table->index('tugas_tambahan_id');
            $table->index('penugasan_mandiri_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_progress');
    }
};

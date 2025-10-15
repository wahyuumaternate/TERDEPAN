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
        Schema::create('knj_nilai_tahunan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('master_pegawai');
            $table->year('tahun')->comment('Tahun penilaian');
            $table->decimal('rata_rata_bulanan', 5, 2)->default(0)->comment('Rata-rata dari 12 bulan');
            $table->decimal('nilai_tahunan', 5, 2)->default(0)->comment('Nilai tahunan final');
            $table->enum('kategori_nilai', ['Sangat_Baik', 'Baik', 'Cukup', 'Kurang', 'Sangat_Kurang'])->nullable();
            $table->text('catatan')->nullable()->comment('Catatan evaluasi tahunan');
            $table->timestamps();

            $table->unique(['pegawai_id', 'tahun']);
            $table->index('kategori_nilai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_nilai_tahunan');
    }
};

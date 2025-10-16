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
        Schema::create('knj_nilai_bulanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('master_pegawai');
            $table->year('tahun')->comment('Tahun penilaian');
            $table->tinyInteger('bulan')->comment('Bulan penilaian 1-12');
            $table->decimal('nilai_tugas_pokok', 5, 2)->default(0)->comment('Nilai tugas pokok 60-70%');
            $table->decimal('nilai_tugas_harian', 5, 2)->default(0)->comment('Nilai tugas harian 20-30%');
            $table->decimal('nilai_tugas_tambahan', 5, 2)->default(0)->comment('Nilai tugas tambahan max 20%');
            $table->decimal('total_penalti', 5, 2)->default(0)->comment('Total penalty');
            $table->decimal('total_bonus', 5, 2)->default(0)->comment('Total bonus');
            $table->decimal('nilai_total', 5, 2)->default(0)->comment('Nilai total 0-100');
            $table->enum('kategori_nilai', ['Sangat_Baik', 'Baik', 'Cukup', 'Kurang', 'Sangat_Kurang'])->nullable();
            $table->boolean('is_approved')->default(false)->comment('TRUE jika sudah approved');
            $table->foreignId('approved_by')->nullable()->constrained('master_pegawai')->comment('Atasan yang approve');
            $table->text('catatan_atasan')->nullable()->comment('Catatan dari atasan');
            $table->timestamp('approved_at')->nullable()->comment('Waktu approval');
            $table->timestamps();

            $table->unique(['pegawai_id', 'tahun', 'bulan']);
            $table->index('kategori_nilai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_nilai_bulanan');
    }
};

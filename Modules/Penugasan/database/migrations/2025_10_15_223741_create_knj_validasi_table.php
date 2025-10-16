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
        Schema::create('knj_validasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_pokok_id')->nullable()->constrained('knj_tugas_pokok')->comment('Validasi tugas pokok if applicable');
            $table->foreignId('tugas_harian_id')->nullable()->constrained('knj_tugas_harian')->comment('Validasi tugas harian if applicable');
            $table->foreignId('tugas_tambahan_id')->nullable()->constrained('knj_tugas_tambahan')->comment('Validasi tugas tambahan if applicable');
            $table->foreignId('penugasan_mandiri_id')->nullable()->constrained('knj_penugasan_mandiri')->comment('Validasi penugasan mandiri if applicable');
            $table->foreignId('validator_id')->constrained('master_pegawai')->comment('Atasan yang validasi');
            $table->enum('hasil_validasi', ['Terima', 'Minta_Revisi', 'Tolak'])->default('Terima');
            $table->text('catatan')->nullable()->comment('Catatan dari validator');
            $table->tinyInteger('penilaian_kualitas')->comment('Score 1-5, CHECK >=1 AND <=5');
            $table->boolean('kesesuaian_target')->default(true)->comment('TRUE jika sesuai target');
            $table->decimal('nilai_akhir', 5, 2)->comment('Nilai hasil kalkulasi sistem');
            $table->date('tanggal_validasi')->comment('Tanggal validasi');
            $table->timestamps();

            $table->index(['validator_id', 'tanggal_validasi']);
            $table->index('tugas_pokok_id');
            $table->index('tugas_harian_id');
            $table->index('tugas_tambahan_id');
            $table->index('penugasan_mandiri_id');

            // Check constraint for penilaian_kualitas >= 1 and <= 5 akan diimplementasikan di level aplikasi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_validasi');
    }
};

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
        // Tabel untuk catatan monitoring
        if (!Schema::hasTable('knj_catatan_monitoring')) {
            Schema::create('knj_catatan_monitoring', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pegawai_id');
                $table->unsignedBigInteger('tugas_id')->nullable();
                $table->enum('tugas_type', ['tugas_harian', 'tugas_tambahan'])->nullable();
                $table->enum('jenis_catatan', ['monitoring', 'revisi', 'feedback']);
                $table->text('isi_catatan');

                $table->index(['pegawai_id', 'tugas_type']);
                $table->index(['jenis_catatan']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_catatan_monitoring');
    }
};

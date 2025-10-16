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
        Schema::create('knj_indikator_tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_pokok_id')->constrained('knj_tugas_pokok');
            $table->string('nama_indikator')->comment('Nama indikator');
            $table->string('satuan', 30)->comment('Satuan pengukuran');
            $table->decimal('target', 10, 2)->comment('Target value');
            $table->decimal('realisasi', 10, 2)->default(0)->comment('Realisasi actual, update dari progress');
            $table->timestamps();

            $table->index('tugas_pokok_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_indikator_tugas');
    }
};

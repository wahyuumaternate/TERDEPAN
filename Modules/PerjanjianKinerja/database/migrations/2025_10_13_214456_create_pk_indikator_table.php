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
        Schema::create('pk_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sasaran_id')->constrained('pk_sasaran');
            $table->text('indikator_sasaran')->comment('Deskripsi indikator');
            $table->string('satuan', 30)->comment('Satuan: persen, dokumen, kegiatan');
            $table->decimal('target_value', 10, 2)->comment('Nilai target yang harus dicapai');
            $table->text('keterangan')->nullable()->comment('Keterangan tambahan');
            $table->timestamps();
            
            $table->index('sasaran_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pk_indikator');
    }
};

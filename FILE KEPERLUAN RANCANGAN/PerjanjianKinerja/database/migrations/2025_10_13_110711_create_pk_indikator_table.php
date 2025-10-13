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
            $table->unsignedBigInteger('sasaran_id')->comment('Foreign key ke PK_SASARAN');
            $table->text('indikator_sasaran')->comment('Deskripsi indikator');
            $table->string('satuan', 50)->comment('Satuan: persen, dokumen, kegiatan');
            $table->decimal('target_value', 15, 2)->comment('Nilai target yang harus dicapai');
            $table->text('keterangan')->nullable()->comment('Keterangan tambahan');
            $table->timestamps();

            // Foreign keys
            $table->foreign('sasaran_id')->references('id')->on('pk_sasaran')->onDelete('cascade');

            // Indexes
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
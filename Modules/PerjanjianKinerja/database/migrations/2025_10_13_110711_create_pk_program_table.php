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
        Schema::create('pk_program', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('indikator_id')->comment('Foreign key ke PK_INDIKATOR');
            $table->integer('urutan')->comment('Urutan program');
            $table->string('kode_program', 50)->nullable()->comment('Kode dari SIPD/e-budgeting');
            $table->string('nama_program')->comment('Nama lengkap program');
            $table->decimal('anggaran', 20, 2)->default(0)->comment('Anggaran dalam rupiah');
            $table->timestamps();

            // Foreign keys
            $table->foreign('indikator_id')->references('id')->on('pk_indikator')->onDelete('cascade');

            // Indexes
            $table->index(['indikator_id', 'urutan']);
            $table->index('kode_program');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pk_program');
    }
};

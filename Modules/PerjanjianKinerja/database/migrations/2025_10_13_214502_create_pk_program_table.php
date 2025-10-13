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
            $table->foreignId('indikator_id')->constrained('pk_indikator');
            $table->integer('urutan')->comment('Urutan program');
            $table->string('kode_program', 30)->comment('Kode dari SIPD/e-budgeting');
            $table->string('nama_program')->comment('Nama lengkap program');
            $table->decimal('anggaran', 15, 2)->default(0)->comment('Anggaran dalam rupiah');
            $table->timestamps();
            
            $table->index('indikator_id');
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

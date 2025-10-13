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
        Schema::create('pk_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_id')->comment('Foreign key ke PK_PROGRAM');
            $table->integer('urutan')->comment('Urutan kegiatan');
            $table->string('kode_kegiatan', 50)->unique()->comment('Kode kegiatan unik');
            $table->string('nama_kegiatan')->comment('Nama lengkap kegiatan');
            $table->decimal('anggaran', 20, 2)->default(0)->comment('Anggaran kegiatan');
            $table->timestamps();

            // Foreign keys
            $table->foreign('program_id')->references('id')->on('pk_program')->onDelete('cascade');

            // Indexes
            $table->index(['program_id', 'urutan']);
            $table->index('kode_kegiatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pk_kegiatan');
    }
};
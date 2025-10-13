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
            $table->foreignId('program_id')->constrained('pk_program');
            $table->integer('urutan')->comment('Urutan kegiatan');
            $table->string('kode_kegiatan', 30)->unique()->comment('Kode kegiatan unik');
            $table->string('nama_kegiatan')->comment('Nama lengkap kegiatan');
            $table->decimal('anggaran', 15, 2)->default(0)->comment('Anggaran kegiatan');
            $table->timestamps();
            
            $table->index('program_id');
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

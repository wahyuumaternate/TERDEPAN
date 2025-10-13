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
        Schema::create('pk_sub_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('pk_kegiatan');
            $table->integer('urutan')->comment('Urutan sub kegiatan');
            $table->string('kode_sub_kegiatan', 30)->unique()->comment('Kode sub kegiatan unik');
            $table->string('nama_sub_kegiatan')->comment('Nama lengkap sub kegiatan');
            $table->decimal('anggaran', 15, 2)->default(0)->comment('Anggaran sub kegiatan');
            $table->integer('target_value')->comment('Target output/outcome');
            $table->string('satuan', 30)->comment('Satuan: Dokumen, Kegiatan, Orang');
            $table->timestamps();
            
            $table->index('kegiatan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pk_sub_kegiatan');
    }
};

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
            $table->unsignedBigInteger('kegiatan_id')->comment('Foreign key ke PK_KEGIATAN');
            $table->integer('urutan')->comment('Urutan sub kegiatan');
            $table->string('kode_sub_kegiatan', 50)->unique()->comment('Kode sub kegiatan unik');
            $table->string('nama_sub_kegiatan')->comment('Nama lengkap sub kegiatan');
            $table->decimal('anggaran', 20, 2)->default(0)->comment('Anggaran sub kegiatan');
            $table->integer('target_value')->default(0)->comment('Target output/outcome');
            $table->string('satuan', 50)->comment('Satuan: Dokumen, Kegiatan, Orang');
            $table->timestamps();

            // Foreign keys
            $table->foreign('kegiatan_id')->references('id')->on('pk_kegiatan')->onDelete('cascade');

            // Indexes
            $table->index(['kegiatan_id', 'urutan']);
            $table->index('kode_sub_kegiatan');
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

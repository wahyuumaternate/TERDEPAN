<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_jenis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('doc_kategori')->cascadeOnDelete();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100);
            $table->string('folder_pattern')->default('/{bidang}/{jenis}/{year}/{month}/');
            $table->string('nomor_format')->nullable();
            $table->string('allowed_ext')->default('pdf,docx,xlsx');
            $table->integer('max_size_mb')->default(10);
            $table->boolean('perlu_nomor')->default(false);
            $table->timestamps();

            $table->index(['kode', 'kategori_id']);
        });

       
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_jenis');
       
    }
};

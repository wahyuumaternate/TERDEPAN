<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_file', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('doc_dokumen')->cascadeOnDelete();
            $table->integer('version')->default(1);
            $table->string('nama_file');
            $table->string('file_path');
            $table->string('extension', 10);
            $table->integer('size_kb');
            $table->string('hash', 64);
            $table->text('keterangan')->nullable();
            $table->boolean('is_current')->default(true);
            $table->foreignId('uploaded_by')->constrained('master_pegawai')->restrictOnDelete();
            $table->timestamps();
            
            $table->index(['dokumen_id', 'version', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_file');
    }
};
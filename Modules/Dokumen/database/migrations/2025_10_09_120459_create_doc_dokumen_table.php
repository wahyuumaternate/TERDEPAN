<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_dokumen', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->unique()->nullable();
            $table->foreignId('folder_id')->constrained('doc_folder')->cascadeOnDelete();
            $table->foreignId('jenis_id')->constrained('doc_jenis')->restrictOnDelete();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_dokumen');
            $table->string('nomor_surat')->nullable();
            $table->enum('status', ['Draft', 'Final', 'Archived'])->default('Draft');
            $table->integer('version')->default(1);
            $table->integer('views')->default(0);
            $table->integer('downloads')->default(0);
            $table->foreignId('uploaded_by')->constrained('master_pegawai')->restrictOnDelete();
            $table->timestamps();
            
            $table->index(['nomor', 'jenis_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_dokumen');
    }
};
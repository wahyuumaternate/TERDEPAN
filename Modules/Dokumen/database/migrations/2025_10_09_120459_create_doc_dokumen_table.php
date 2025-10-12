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
        Schema::create('doc_template', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_id')->constrained('doc_jenis')->onDelete('cascade');
            $table->string('nama');
            $table->string('kode')->unique();
            $table->text('deskripsi')->nullable();
            $table->longText('content'); // HTML content dengan placeholders
            $table->json('variables')->nullable(); // List variable yang tersedia
            $table->string('file_template')->nullable(); // Path ke file template (DOCX/PDF)
            $table->enum('format_output', ['html', 'docx', 'pdf'])->default('html');
            $table->boolean('is_active')->default(true);
            $table->text('header')->nullable(); // Header template
            $table->text('footer')->nullable(); // Footer template
            $table->json('settings')->nullable(); // Page settings, margins, etc
            $table->foreignId('created_by')->nullable()->constrained('master_pegawai')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('master_pegawai')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['jenis_id', 'is_active']);
        });

        // Table untuk tracking generated documents
        Schema::create('doc_template_generated', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('doc_template')->onDelete('cascade');
            $table->foreignId('dokumen_id')->nullable()->constrained('doc_dokumen')->onDelete('set null');
            $table->foreignId('user_id')->constrained('master_pegawai')->onDelete('cascade');
            $table->json('data_variables'); // Data yang digunakan untuk generate
            $table->string('file_path')->nullable(); // Path hasil generate
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->index(['template_id', 'user_id']);
        });
    }



    public function down(): void
    {
        Schema::dropIfExists('doc_dokumen');
        Schema::dropIfExists('doc_template_generated');
        Schema::dropIfExists('doc_template');
    }
};

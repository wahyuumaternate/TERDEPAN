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
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->jsonb('metadata')->nullable()->comment('Flexible metadata storage');
            $table->date('tanggal_dokumen')->nullable();
            $table->string('nomor_surat')->nullable();
            $table->enum('status', ['Draft', 'Final', 'Archived'])->default('Draft');
            $table->integer('version')->default(1);
            $table->integer('views')->default(0);
            $table->integer('downloads')->default(0);
            $table->foreignId('uploaded_by')->constrained('master_pegawai')->restrictOnDelete();

            // Polymorphic relation untuk attach ke tugas, PK, dll
            $table->string('related_type')->nullable()->comment('Polymorphic type: tugas_harian, tugas_tambahan, etc');
            $table->unsignedBigInteger('related_id')->nullable()->comment('Polymorphic ID');
            $table->boolean('is_public')->default(false)->comment('Public access flag');

            $table->timestamps();

            $table->index(['nomor', 'status']);
            $table->index(['related_type', 'related_id']);
            $table->index('folder_id');
        });
        // Table untuk template dokumen (optional - bisa dihapus jika tidak dipakai)
        Schema::create('doc_template', function (Blueprint $table) {
            $table->id();
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

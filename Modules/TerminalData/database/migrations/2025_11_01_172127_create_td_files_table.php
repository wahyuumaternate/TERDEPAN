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
        Schema::create('td_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('folder_id')->index();
            $table->foreignId('bidang_id')->nullable()->constrained('master_bidang')->nullOnDelete();
            $table->foreignId('sub_bidang_id')->nullable()->constrained('master_sub')->nullOnDelete();

            // File Basic Info
            $table->string('name');
            $table->string('original_name')->comment('Original filename when uploaded');
            $table->text('description')->nullable();

            // File Details
            $table->string('mime_type');
            $table->string('extension', 10);
            $table->unsignedBigInteger('size')->comment('File size in bytes');
            $table->string('storage_path')->comment('Path in storage');
            $table->string('hash', 64)->comment('SHA256 hash for integrity');
            $table->string('thumbnail_path')->nullable();

            // Document Specific
            $table->string('document_number')->nullable()->unique();
            $table->date('document_date')->nullable();
            $table->string('document_type')->nullable()->comment('Surat, SK, Memo, etc');
            $table->enum('status', ['draft', 'review', 'approved', 'final', 'archived'])->default('draft');

            // Versioning
            $table->unsignedInteger('version')->default(1);
            $table->uuid('original_file_id')->nullable()->comment('ID file asli untuk tracking versions');
            $table->boolean('is_latest_version')->default(true);
            $table->text('version_notes')->nullable();

            // Relations (Polymorphic) - Support both UUID and BigInt
            $table->string('attachable_type')->nullable()->index();
            $table->string('attachable_id')->nullable()->index()->comment('Support UUID and BigInt');

            // Permissions & Status
            $table->boolean('is_public')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_locked')->default(false)->comment('Prevent editing/deletion');
            $table->boolean('is_encrypted')->default(false);

            // Stats
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('downloads')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamp('last_downloaded_at')->nullable();

            // Metadata
            $table->json('metadata')->nullable()->comment('Custom attributes');
            $table->json('extracted_content')->nullable()->comment('OCR/extracted text for search');

            // Ownership
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->restrictOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['folder_id', 'status']);
            $table->index(['original_file_id', 'version']);
            $table->index(['created_by', 'is_starred']);
            $table->index(['document_number', 'document_date']);
            $table->fullText(['name', 'description', 'original_name']);

            // Foreign keys
            $table->foreign('folder_id')->references('id')->on('td_folders')->cascadeOnDelete();
        });

        // Add self-referential foreign key after table creation
        Schema::table('td_files', function (Blueprint $table) {
            $table->foreign('original_file_id')->references('id')->on('td_files')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('td_files');
    }
};

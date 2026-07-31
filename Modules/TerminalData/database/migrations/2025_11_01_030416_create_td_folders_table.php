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
        Schema::create('td_folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->nullable()->index();
            $table->foreignId('bidang_id')->nullable()->constrained('master_bidang')->nullOnDelete();

            // Folder Info
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('path')->index();
            $table->integer('level')->default(0);
            $table->string('color', 7)->nullable()->comment('Hex color untuk UI');
            $table->string('icon')->nullable()->comment('Icon name/class');

            // Permissions & Settings
            $table->boolean('is_public')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_locked')->default(false)->comment('Prevent deletion/modification');
            $table->boolean('is_system')->default(false)->comment('System generated folder');

            // Stats
            $table->unsignedInteger('total_files')->default(0);
            $table->unsignedInteger('total_subfolders')->default(0);
            $table->unsignedBigInteger('total_size')->default(0)->comment('Total size in bytes');

            // Metadata
            $table->json('settings')->nullable()->comment('Custom folder settings');

            // Ownership
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->restrictOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['parent_id', 'level']);
            $table->index(['created_by', 'is_starred']);
            $table->fullText(['name', 'description']);
        });

        // Add self-referential foreign key after table creation
        // Schema::table('td_folders', function (Blueprint $table) {
        //     $table->foreign('parent_id')->references('id')->on('td_folders')->nullOnDelete();
        // });
    }

    public function down(): void
    {
        Schema::dropIfExists('td_folders');
    }
};

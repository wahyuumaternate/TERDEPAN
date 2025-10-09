<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_folder', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('doc_folder')->nullOnDelete();
            $table->foreignId('bidang_id')->nullable()->constrained('master_bidang')->nullOnDelete();
            $table->string('nama', 100);
            $table->string('path')->unique();
            $table->integer('level')->default(0);
            $table->boolean('is_auto')->default(false);
            $table->integer('total_files')->default(0);
            $table->foreignId('created_by')->constrained('master_pegawai')->cascadeOnDelete();
            $table->timestamps();
            
            $table->index(['parent_id', 'bidang_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_folder');
    }
};
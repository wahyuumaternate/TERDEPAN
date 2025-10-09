<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('doc_dokumen')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('master_pegawai')->cascadeOnDelete();
            $table->enum('action', ['View', 'Download', 'Upload', 'Edit', 'Delete']);
            $table->string('ip_address', 45);
            $table->timestamps();
            
            $table->index(['dokumen_id', 'user_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_log');
    }
};
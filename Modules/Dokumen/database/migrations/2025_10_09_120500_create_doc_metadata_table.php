<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('doc_dokumen')->cascadeOnDelete();
            $table->string('key', 100);
            $table->text('value');
            $table->timestamps();
            
            $table->index(['dokumen_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_metadata');
    }
};
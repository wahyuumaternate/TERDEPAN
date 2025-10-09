<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_nomor_counter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_id')->constrained('doc_jenis')->cascadeOnDelete();
            $table->foreignId('bidang_id')->constrained('master_bidang')->cascadeOnDelete();
            $table->integer('tahun');
            $table->integer('counter')->default(0);
            $table->timestamps();

            $table->unique(['jenis_id', 'bidang_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_nomor_counter');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_kategori', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50);
            $table->string('icon', 50)->nullable();
            $table->string('warna', 7)->default('#2563eb');
            $table->integer('urutan')->default(0);
            $table->timestamps();
            
            $table->index('urutan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_kategori');
    }
};
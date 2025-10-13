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
        Schema::create('pk_sasaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perjanjian_kinerja_id')->constrained('pk_perjanjian_kinerja');
            $table->integer('urutan')->comment('Urutan sasaran: 1, 2, 3...');
            $table->text('sasaran_strategis')->comment('Deskripsi sasaran strategis');
            $table->timestamps();
            
            $table->unique(['perjanjian_kinerja_id', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pk_sasaran');
    }
};

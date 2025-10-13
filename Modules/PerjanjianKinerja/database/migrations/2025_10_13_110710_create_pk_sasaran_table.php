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
            $table->unsignedBigInteger('perjanjian_kinerja_id')->comment('Foreign key ke PK_PERJANJIAN_KINERJA');
            $table->integer('urutan')->comment('Urutan sasaran: 1, 2, 3...');
            $table->text('sasaran_strategis')->comment('Deskripsi sasaran strategis');
            $table->timestamps();

            // Foreign keys
            $table->foreign('perjanjian_kinerja_id', 'fk_sasaran_perjanjian')
                ->references('id')
                ->on('pk_perjanjian_kinerja')
                ->onDelete('cascade');

            // Indexes
            $table->index(['perjanjian_kinerja_id', 'urutan']);
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

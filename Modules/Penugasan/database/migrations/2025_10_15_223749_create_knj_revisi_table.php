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
        Schema::create('knj_revisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validasi_id')->constrained('knj_validasi')->comment('Foreign key ke KNJ_VALIDASI');
            $table->text('bagian_revisi')->comment('Mandatory: bagian yang perlu diperbaiki');
            $table->text('catatan_detail')->comment('Catatan detail');
            $table->text('panduan_perbaikan')->nullable()->comment('Panduan perbaikan');
            $table->timestamp('deadline_revisi')->comment('Auto +24 jam dari request');
            $table->boolean('is_terlambat')->default(false)->comment('TRUE jika lewat deadline');
            $table->decimal('penalty_nilai', 5, 2)->default(0)->comment('Penalty jika terlambat');
            $table->timestamp('submitted_at')->nullable()->comment('Waktu submit revisi');
            $table->timestamps();

            $table->index('validasi_id');
            $table->index('deadline_revisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_revisi');
    }
};

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
        Schema::create('pk_template', function (Blueprint $table) {
            $table->id();
            $table->string('kode_template')->unique()->comment('Kode unik: TPK-KB-2024, TPK-SB-2024');
            $table->string('nama_template')->comment('Nama deskriptif: PK Eselon II 2024');
            $table->foreignId('jabatan_id')->constrained('master_jabatan')->comment('Template untuk jabatan ini');
            $table->year('tahun')->comment('Tahun berlaku template');
            $table->text('kop_surat_html')->comment('HTML kop surat dengan logo');
            $table->text('header_template')->comment('Template header dokumen');
            $table->text('pernyataan_pembuka')->comment('Template pembuka perjanjian');
            $table->text('pernyataan_penutup')->comment('Template penutup');
            $table->text('footer_template')->comment('Template footer dengan area TTD');
            $table->enum('page_size', ['A4', 'Legal', 'Letter'])->default('A4');
            $table->enum('orientation', ['Portrait', 'Landscape'])->default('Portrait');
            $table->integer('versi')->default(1)->comment('Version number, increment untuk changes');
            $table->boolean('is_active')->default(true)->comment('TRUE=aktif, hanya 1 aktif per jabatan+tahun');
            $table->timestamps();
            
            $table->index(['jabatan_id', 'tahun', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pk_template');
    }
};

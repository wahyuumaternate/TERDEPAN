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
            $table->string('kode_template', 50)->unique()->comment('Kode unik: TPK-KB-2024, TPK-SB-2024');
            $table->string('nama_template')->comment('Nama deskriptif: PK Eselon II 2024');
            $table->unsignedBigInteger('jabatan_id')->comment('Template untuk jabatan ini');
            $table->year('tahun')->comment('Tahun berlaku template');
            $table->text('kop_surat_html')->nullable()->comment('HTML kop surat dengan logo');
            $table->text('header_template')->nullable()->comment('Template header dokumen');
            $table->text('pernyataan_pembuka')->nullable()->comment('Template pembuka perjanjian');
            $table->text('pernyataan_penutup')->nullable()->comment('Template penutup');
            $table->text('footer_template')->nullable()->comment('Template footer dengan area TTD');
            $table->enum('page_size', ['A4', 'Legal', 'Letter'])->default('A4')->comment('Ukuran halaman');
            $table->enum('orientation', ['Portrait', 'Landscape'])->default('Portrait')->comment('Orientasi halaman');
            $table->integer('versi')->default(1)->comment('Version number, increment untuk changes');
            $table->boolean('is_active')->default(true)->comment('TRUE=aktif, hanya 1 aktif per jabatan+tahun');
            $table->timestamps();

            // Foreign keys
            $table->foreign('jabatan_id')->references('id')->on('master_jabatan')->onDelete('restrict');

            // Indexes
            $table->index(['jabatan_id', 'tahun', 'is_active']);
            $table->index('is_active');
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

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
        Schema::create('pk_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perjanjian_kinerja_id')->constrained('pk_perjanjian_kinerja');
            $table->enum('jenis_dokumen', ['Pernyataan', 'Formulir', 'Lampiran'])->default('Formulir');
            $table->string('nomor_dokumen')->comment('Sama dengan nomor_perjanjian');
            $table->string('file_name')->comment('Format: PK_NIP_2024_v1.pdf');
            $table->string('file_path')->comment('Full path: storage/perjanjian/2024/...');
            $table->string('file_hash')->comment('SHA256 untuk integrity check');
            $table->integer('file_size_kb')->comment('Ukuran file dalam KB');
            $table->integer('versi')->default(1)->comment('Version number, increment saat re-generate');
            $table->integer('total_pages')->default(1)->comment('Total halaman PDF');
            $table->foreignId('generated_by')->constrained('master_pegawai')->comment('User yang trigger generate');
            $table->timestamp('generated_at')->useCurrent()->comment('Waktu generate');
            $table->boolean('is_latest')->default(true)->comment('TRUE=versi terbaru');
            $table->foreignId('dokumen_id')->nullable()->constrained('doc_dokumen')->comment('Foreign key ke DOC_DOKUMEN untuk integrasi');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['perjanjian_kinerja_id', 'is_latest']);
            $table->index('versi');
            $table->index('dokumen_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pk_dokumen');
    }
};

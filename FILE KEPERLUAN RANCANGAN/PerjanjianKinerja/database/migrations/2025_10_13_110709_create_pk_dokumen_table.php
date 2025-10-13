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
            $table->unsignedBigInteger('perjanjian_kinerja_id')->comment('Foreign key ke PK_PERJANJIAN_KINERJA');
            $table->enum('jenis_dokumen', ['Pernyataan', 'Formulir', 'Lampiran'])->comment('Jenis dokumen');
            $table->string('nomor_dokumen', 100)->comment('Sama dengan nomor_perjanjian');
            $table->string('file_name')->comment('Format: PK_NIP_2024_v1.pdf');
            $table->string('file_path')->comment('Full path: storage/perjanjian/2024/...');
            $table->string('file_hash', 64)->comment('SHA256 untuk integrity check');
            $table->integer('file_size_kb')->comment('Ukuran file dalam KB');
            $table->integer('versi')->default(1)->comment('Version number, increment saat re-generate');
            $table->integer('total_pages')->nullable()->comment('Total halaman PDF');
            $table->unsignedBigInteger('generated_by')->comment('User yang trigger generate');
            $table->timestamp('generated_at')->comment('Waktu generate');
            $table->boolean('is_latest')->default(true)->comment('TRUE=versi terbaru');
            $table->unsignedBigInteger('dokumen_id')->nullable()->comment('Foreign key ke DOC_DOKUMEN untuk integrasi');
            $table->timestamp('created_at')->useCurrent();

            // Foreign keys
            $table->foreign('perjanjian_kinerja_id')->references('id')->on('pk_perjanjian_kinerja')->onDelete('cascade');
            $table->foreign('generated_by')->references('id')->on('master_pegawai')->onDelete('restrict');

            // Note: Uncomment this if doc_dokumen table exists
            // $table->foreign('dokumen_id')->references('id')->on('doc_dokumen')->onDelete('set null');

            // Indexes
            $table->index(['perjanjian_kinerja_id', 'is_latest']);
            $table->index(['jenis_dokumen', 'versi']);
            $table->index('nomor_dokumen');
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

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
        Schema::create('pk_perjanjian_kinerja', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_perjanjian')->unique()->comment('Nomor unik dari counter: PK/BAPPEDA/2024/001');
            $table->foreignId('pegawai_id')->constrained('master_pegawai')->comment('Pihak pertama pegawai');
            $table->foreignId('atasan_id')->constrained('master_pegawai')->comment('Pihak kedua atasan');
            $table->foreignId('template_id')->constrained('pk_template')->comment('Template yang digunakan');
            $table->year('tahun')->comment('Tahun perjanjian');
            $table->date('periode_mulai')->comment('Tanggal mulai biasanya 1 Jan');
            $table->date('periode_selesai')->comment('Tanggal akhir biasanya 31 Des');
            $table->string('tempat_ttd', 50)->default('Kuningan')->comment('Tempat TTD default Kuningan');
            $table->date('tanggal_ttd')->nullable()->comment('Tanggal TTD, NULL jika belum');
            $table->decimal('total_anggaran', 15, 2)->default(0)->comment('Total anggaran dari sum program, auto-calculate');
            $table->enum('status_dokumen', ['Draft', 'Generated', 'Menunggu_TTD', 'Aktif', 'Selesai', 'Dibatalkan'])->default('Draft');
            $table->text('catatan')->nullable()->comment('Catatan tambahan');
            $table->boolean('is_locked')->default(false)->comment('TRUE=tidak bisa edit setelah TTD');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['pegawai_id', 'tahun']);
            $table->index('atasan_id');
            $table->index('status_dokumen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pk_perjanjian_kinerja');
    }
};

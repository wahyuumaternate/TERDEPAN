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
            $table->string('nomor_perjanjian', 100)->unique()->comment('Nomor unik dari counter: PK/BAPPEDA/2024/001');
            $table->unsignedBigInteger('pegawai_id')->comment('Pihak pertama pegawai');
            $table->unsignedBigInteger('atasan_id')->comment('Pihak kedua atasan');
            $table->unsignedBigInteger('template_id')->comment('Template yang digunakan');
            $table->year('tahun')->comment('Tahun perjanjian');
            $table->date('periode_mulai')->comment('Tanggal mulai biasanya 1 Jan');
            $table->date('periode_selesai')->comment('Tanggal akhir biasanya 31 Des');
            $table->string('tempat_ttd', 100)->default('Sofifi')->comment('Tempat TTD default Sofifi');
            $table->date('tanggal_ttd')->nullable()->comment('Tanggal TTD, NULL jika belum');
            $table->decimal('total_anggaran', 20, 2)->default(0)->comment('Total anggaran dari sum program, auto-calculate');
            $table->enum('status_dokumen', ['Draft', 'Generated', 'Menunggu_TTD', 'Aktif', 'Selesai', 'Dibatalkan'])
                ->default('Draft')
                ->comment('Status dokumen perjanjian');
            $table->text('catatan')->nullable()->comment('Catatan tambahan');
            $table->boolean('is_locked')->default(false)->comment('TRUE=tidak bisa edit setelah TTD');
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('pegawai_id')->references('id')->on('master_pegawai')->onDelete('restrict');
            $table->foreign('atasan_id')->references('id')->on('master_pegawai')->onDelete('restrict');
            $table->foreign('template_id')->references('id')->on('pk_template')->onDelete('restrict');

            // Indexes
            $table->index(['pegawai_id', 'tahun']);
            $table->index(['status_dokumen', 'tahun']);
            $table->index('is_locked');
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

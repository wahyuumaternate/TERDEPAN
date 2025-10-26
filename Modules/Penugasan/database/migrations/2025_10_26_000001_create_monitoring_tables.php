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
        // Tabel untuk catatan monitoring
        if (!Schema::hasTable('knj_catatan_monitoring')) {
            Schema::create('knj_catatan_monitoring', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pegawai_id');
                $table->unsignedBigInteger('tugas_id')->nullable();
                $table->enum('tugas_type', ['tugas_pokok', 'tugas_harian', 'tugas_tambahan'])->nullable();
                $table->unsignedBigInteger('catatan_oleh');
                $table->timestamp('tanggal_catatan');
                $table->enum('jenis_catatan', ['monitoring', 'revisi', 'feedback']);
                $table->text('isi_catatan');
                $table->timestamps();

                $table->foreign('pegawai_id')->references('id')->on('master_pegawai')->onDelete('cascade');
                $table->foreign('catatan_oleh')->references('id')->on('master_pegawai')->onDelete('cascade');

                $table->index(['pegawai_id', 'tugas_type']);
                $table->index(['catatan_oleh']);
            });
        }

        // Tabel untuk histori revisi
        if (!Schema::hasTable('knj_histori_revisi')) {
            Schema::create('knj_histori_revisi', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tugas_id');
                $table->enum('tugas_type', ['tugas_pokok', 'tugas_harian', 'tugas_tambahan']);
                $table->unsignedBigInteger('revisi_oleh');
                $table->timestamp('tanggal_revisi');
                $table->string('field_diubah');
                $table->text('nilai_lama')->nullable();
                $table->text('nilai_baru')->nullable();
                $table->text('catatan_revisi')->nullable();
                $table->timestamps();

                $table->foreign('revisi_oleh')->references('id')->on('master_pegawai')->onDelete('cascade');

                $table->index(['tugas_id', 'tugas_type']);
                $table->index(['revisi_oleh']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_histori_revisi');
        Schema::dropIfExists('knj_catatan_monitoring');
    }
};

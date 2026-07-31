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
        Schema::create('knj_tugas_pokok', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignId('pegawai_id')->constrained('users');

            // Referensi opsional ke modul PerjanjianKinerja (bisa dinonaktifkan secara
            // independen — lihat modules_statuses.json). Sengaja TANPA foreign key
            // constraint di level database, karena tabel pk_perjanjian_kinerja/pk_indikator
            // tidak selalu ada saat modul PerjanjianKinerja nonaktif. Integritas referensial
            // untuk kolom ini dijaga di level aplikasi, bukan database.
            $table->unsignedBigInteger('perjanjian_kinerja_id')->nullable()
                ->comment('Opsional — diisi jika Tugas Pokok berasal dari indikator Perjanjian Kinerja');
            $table->unsignedBigInteger('indikator_id')->nullable()
                ->comment('Opsional — 1 indikator = 1 tugas pokok, jika berasal dari PK');

            // Copied data from PK (snapshot)
            $table->string('nama_tugas');
            $table->text('deskripsi');
            $table->decimal('bobot_persen', 5, 2)->comment('60-70% dari PK');
            $table->decimal('target_value', 10, 2);
            $table->string('satuan', 30);

            // Periode Waktu
            $table->date('tanggal_mulai')->comment('Tanggal mulai');
            $table->date('tanggal_selesai')->comment('Tanggal target selesai');

            // Status
            $table->enum('status', ['pending', 'dikerjakan', 'selesai'])->default('pending');

            // Progress
            $table->decimal('progress_persen', 5, 2)->default(0)->comment('Progress 0-100%, auto-calculated dari tugas harian');

            // Nilai
            $table->decimal('nilai_akhir', 5, 2)->nullable()->comment('auto-calculated rata-rata dari tugas harian');

            // Acceptance
            $table->timestamp('diterima_at')->nullable()->comment('Waktu pegawai terima');

            // timestamps dan soft delete
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pegawai_id', 'status']);
            $table->index('perjanjian_kinerja_id');
            $table->index('tanggal_selesai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_tugas_pokok');
    }
};

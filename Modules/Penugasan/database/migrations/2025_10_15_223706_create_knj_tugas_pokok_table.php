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
            $table->id();
            $table->foreignId('pegawai_id')->constrained('master_pegawai')->comment('Penerima tugas');
            $table->foreignId('perjanjian_kinerja_id')->constrained('pk_perjanjian_kinerja')->comment('Source dari PK');
            $table->foreignId('indikator_id')->constrained('pk_indikator')->comment('1 indikator = 1 tugas pokok');

            $table->string('nama_tugas')->comment('Nama/judul tugas');
            $table->text('deskripsi')->comment('Deskripsi lengkap tugas');
            $table->decimal('bobot_persen', 5, 2)->comment('Bobot 60-70%, CHECK >=60 AND <=70');
            $table->date('periode_mulai')->comment('Tanggal mulai');
            $table->date('periode_selesai')->comment('Tanggal target selesai');

            $table->decimal('target_value', 10, 2)->comment('Target yang harus dicapai');
            $table->string('satuan', 30)->comment('Satuan target');
            $table->enum('status', ['Pending', 'Diterima', 'Dikerjakan', 'Selesai', 'Tidak_Selesai', 'Divalidasi'])->default('Pending');
            $table->decimal('progress_persen', 5, 2)->default(0)->comment('Progress 0-100%, auto-calc');

            $table->timestamp('diterima_at')->nullable()->comment('Waktu pegawai terima');
            $table->foreignId('dokumen_lampiran_id')->nullable()->constrained('doc_dokumen')->comment('Foreign key ke DOC_DOKUMEN');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pegawai_id', 'status']);
            $table->index('perjanjian_kinerja_id');
            $table->index('indikator_id');
            $table->index('periode_selesai');

            // Check constraint for bobot_persen >= 60 and <= 70 akan diimplementasikan di level aplikasi
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

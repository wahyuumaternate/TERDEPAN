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
        Schema::create('knj_tugas_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_pokok_id')->constrained('knj_tugas_pokok')->comment('Wajib terkait KNJ_TUGAS_POKOK');
            $table->foreignId('pegawai_id')->constrained('master_pegawai')->comment('Penerima tugas');
            $table->foreignId('pemberi_tugas_id')->constrained('master_pegawai')->comment('Atasan yang assign');
            $table->string('nama_tugas')->comment('Nama tugas');
            $table->text('deskripsi')->comment('Deskripsi tugas');
            $table->enum('periode_type', ['Harian', 'Mingguan'])->default('Harian');
            $table->date('tanggal_mulai')->comment('Tanggal mulai');
            $table->date('deadline')->comment('Deadline tugas');
            $table->decimal('bobot_persen', 5, 2)->comment('Bobot 20-30%');
            $table->decimal('target_value', 10, 2)->comment('Target');
            $table->string('satuan', 30)->comment('Satuan target');
            $table->enum('status', ['Assigned', 'In_Progress', 'Submitted', 'Validated', 'Rejected'])->default('Assigned');
            $table->foreignId('dokumen_lampiran_id')->nullable()->constrained('doc_dokumen')->comment('Foreign key ke DOC_DOKUMEN');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pegawai_id', 'status']);
            $table->index('tugas_pokok_id');
            $table->index('deadline');

            // Check constraint for bobot_persen >= 20 and <= 30 akan diimplementasikan di level aplikasi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_tugas_harian');
    }
};

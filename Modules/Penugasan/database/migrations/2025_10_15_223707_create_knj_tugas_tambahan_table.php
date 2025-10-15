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
        Schema::create('knj_tugas_tambahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('master_pegawai')->comment('Penerima tugas');
            $table->foreignId('pemberi_tugas_id')->constrained('master_pegawai')->comment('Atasan yang assign');
            $table->string('nama_tugas')->comment('Nama tugas');
            $table->text('deskripsi')->comment('Deskripsi tugas');
            $table->text('alasan_penugasan')->comment('Mandatory: kenapa pegawai ini');
            $table->date('deadline')->comment('Deadline tugas');
            $table->decimal('bobot_persen', 5, 2)->comment('Bobot, CHECK total <=20%');
            $table->decimal('target_value', 10, 2)->comment('Target');
            $table->string('satuan', 30)->comment('Satuan');
            $table->enum('status', ['Assigned', 'In_Progress', 'Submitted', 'Validated', 'Rejected'])->default('Assigned');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pegawai_id', 'status']);
            $table->index('pemberi_tugas_id');
            $table->index('deadline');

            // Check constraint for total bobot_persen <= 20% akan diimplementasikan di level aplikasi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_tugas_tambahan');
    }
};

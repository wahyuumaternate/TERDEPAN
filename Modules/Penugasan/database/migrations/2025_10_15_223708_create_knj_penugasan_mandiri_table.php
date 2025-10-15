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
        Schema::create('knj_penugasan_mandiri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('master_pegawai')->comment('Pegawai yang buat');
            $table->foreignId('tugas_pokok_id')->constrained('knj_tugas_pokok')->comment('Tugas pokok yang didukung');
            $table->string('nama_tugas')->comment('Nama tugas mandiri');
            $table->text('deskripsi')->comment('Deskripsi lengkap');
            $table->decimal('target_value', 10, 2)->comment('Target');
            $table->string('satuan', 30)->comment('Satuan');
            $table->date('target_selesai')->comment('Target selesai');
            $table->enum('status', ['Pending_Approval', 'Approved', 'Rejected', 'In_Progress', 'Completed'])->default('Pending_Approval');
            $table->foreignId('approved_by')->nullable()->constrained('master_pegawai')->comment('Atasan yang approve');
            $table->text('alasan_reject')->nullable()->comment('Wajib jika rejected');
            $table->timestamp('approved_at')->nullable()->comment('Waktu approval');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pegawai_id', 'status']);
            $table->index('tugas_pokok_id');
            $table->index('target_selesai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_penugasan_mandiri');
    }
};

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
        Schema::create('knj_delegasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_pokok_id')->nullable()->constrained('knj_tugas_pokok')->comment('Delegasi tugas pokok if applicable');
            $table->foreignId('tugas_harian_id')->nullable()->constrained('knj_tugas_harian')->comment('Delegasi tugas harian if applicable');
            $table->foreignId('tugas_tambahan_id')->nullable()->constrained('knj_tugas_tambahan')->comment('Delegasi tugas tambahan if applicable');
            $table->foreignId('pegawai_asal_id')->constrained('master_pegawai')->comment('Pegawai asal');
            $table->foreignId('pegawai_tujuan_id')->constrained('master_pegawai')->comment('Pegawai tujuan');
            $table->foreignId('delegator_id')->constrained('master_pegawai')->comment('Atasan yang approve delegasi');
            $table->text('alasan_delegasi')->comment('Alasan delegasi');
            $table->decimal('progress_saat_delegasi', 5, 2)->default(0)->comment('Progress saat delegasi untuk hitung penalty');
            $table->decimal('penalty_nilai', 5, 2)->default(0)->comment('Penalty berdasarkan progress');
            $table->text('scope_delegasi')->nullable()->comment('Scope yang didelegasi');
            $table->date('deadline_baru')->comment('Deadline baru setelah delegasi');
            $table->enum('status', ['Pending', 'Accepted', 'In_Progress', 'Completed', 'Rejected'])->default('Pending');
            $table->timestamp('delegated_at')->useCurrent()->comment('Waktu delegasi');
            $table->timestamps();

            $table->index('pegawai_asal_id');
            $table->index('pegawai_tujuan_id');
            $table->index('tugas_pokok_id');
            $table->index('tugas_harian_id');
            $table->index('tugas_tambahan_id');
            $table->index('deadline_baru');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_delegasi');
    }
};

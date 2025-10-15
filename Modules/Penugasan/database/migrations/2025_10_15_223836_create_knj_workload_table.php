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
        Schema::create('knj_workload', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('master_pegawai');
            $table->date('tanggal')->comment('Tanggal snapshot');
            $table->integer('jumlah_tugas_aktif')->default(0)->comment('Jumlah tugas aktif');
            $table->decimal('total_bobot_persen', 5, 2)->default(0)->comment('Total bobot semua tugas');
            $table->integer('deadline_overlap_count')->default(0)->comment('Jumlah deadline yang overlap');
            $table->enum('kategori_beban', ['Normal', 'Moderate', 'Heavy', 'Overload'])->default('Normal');
            $table->decimal('skor_beban', 5, 2)->default(0)->comment('Skor beban 0-100+');
            $table->json('detail_breakdown')->nullable()->comment('Detail breakdown per jenis tugas');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['pegawai_id', 'tanggal']);
            $table->index('kategori_beban');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_workload');
    }
};

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
        Schema::create('pk_periode', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->string('nama_periode');
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['Aktif', 'Selesai', 'Ditutup'])->default('Ditutup');
            $table->boolean('is_active')->default(false);
            $table->foreignId('dibuka_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('dibuka_pada')->nullable();
            $table->foreignId('ditutup_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('ditutup_pada')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('tahun');
            $table->index('status');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pk_periode');
    }
};

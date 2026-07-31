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
        // Jabatan
        Schema::create('master_jabatan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique()->comment('Kode jabatan: KB,SB,KBD,STF,JF,TT');
            $table->string('nama', 100)->comment('Nama lengkap: Kaban, Sekban, Kabid, Staff, Jabatan Fungsional, Tenaga Teknis');
            $table->integer('level')->comment('Level hierarki 1-6 untuk organizational structure');
            $table->boolean('is_struktural')->default(false)->comment('TRUE jika jabatan struktural, FALSE untuk fungsional');
            $table->boolean('bebas_nilai_kinerja')->default(false)->comment('TRUE untuk Tenaga Teknis yang tidak dinilai');
            $table->boolean('is_active')->default(true)->comment('Status aktif, untuk soft disable');
            $table->timestamps();

            $table->index('level');
        });

        // Bidang
        Schema::create('master_bidang', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique()->comment('Kode unik: PLAN, EVAL, DATA, SEKRET');
            $table->string('nama', 100)->comment('Nama lengkap bidang untuk display');
            $table->string('warna', 7)->nullable()->comment('Hex color untuk UI');
            $table->boolean('is_active')->default(true)->comment('TRUE=aktif, FALSE=tidak bisa assign pegawai baru');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('master_sub', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidang_id')->constrained('master_bidang');
            $table->string('nama', 100)->comment('Nama lengkap bidang untuk display');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_sub');
        Schema::dropIfExists('master_bidang');
        Schema::dropIfExists('master_jabatan');
    }
};

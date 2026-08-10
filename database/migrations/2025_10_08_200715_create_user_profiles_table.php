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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nomor_identitas', 18)->unique()->comment('NIP 18 digit atau NIK 16 digit');
            $table->enum('tipe_identitas', ['NIP', 'ID'])->comment('NIP atau ID');
            $table->foreignId('jabatan_id')->constrained('master_jabatan');
            $table->foreignId('bidang_id')->constrained('master_bidang');
            $table->foreignId('sub_bidang_id')->nullable()->constrained('master_sub');
            $table->string('no_telepon', 20)->nullable()->comment('Nomor telepon untuk SMS/WhatsApp');
            $table->string('pangkat', 50)->nullable()->comment('Pangkat PNS: Penata, Pembina, dll');
            $table->string('golongan', 10)->nullable()->comment('Golongan: III/a, III/b, IV/a, dll');
            $table->string('gelar_depan', 20)->nullable()->comment('Dr., Ir., Drs., dll');
            $table->string('gelar_belakang', 20)->nullable()->comment('S.T., M.T., M.Si., dll');
            $table->date('tanggal_lahir')->nullable()->comment('Untuk perhitungan usia dan pensiun');
            $table->enum('jenis_kelamin', ['L', 'P'])->comment('L atau P');
            $table->text('alamat')->nullable()->comment('Alamat lengkap');
            $table->enum('status_kepegawaian', ['PNS', 'PPPK', 'Kontrak'])->comment('PNS, PPPK, Kontrak');
            $table->enum('status_aktif', ['Aktif', 'Nonaktif', 'Cuti', 'Pensiun', 'Pindah'])->default('Aktif')->comment('Aktif, Nonaktif, Cuti, Pensiun, Pindah');
            $table->date('tanggal_masuk')->nullable()->comment('Tanggal mulai bekerja');
            $table->date('tanggal_keluar')->nullable()->comment('Tanggal berhenti, NULL jika masih aktif');
            $table->foreignId('atasan_langsung_id')->nullable()->constrained('users')->comment('Self-reference ke users untuk hierarchy');
            $table->string('foto_profile_path')->nullable()->comment('Path file foto profil');
            $table->timestamp('last_login_at')->nullable()->comment('Timestamp login terakhir untuk audit');
            $table->string('last_login_ip', 45)->nullable()->comment('IP address login terakhir');
            $table->timestamps();
            $table->softDeletes();

            $table->index('bidang_id');
            $table->index('jabatan_id');
            $table->index('status_aktif');
            $table->index('atasan_langsung_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};

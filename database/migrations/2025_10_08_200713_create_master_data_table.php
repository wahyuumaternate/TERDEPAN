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

        // Pegawai
        Schema::create('master_pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_identitas', 18)->unique()->comment('NIP 18 digit atau NIK 16 digit, untuk login');
            $table->enum('tipe_identitas', ['NIP', 'NIK'])->comment('NIP atau NIK');
            $table->string('nama', 100)->comment('Nama lengkap tanpa gelar');
            $table->foreignId('jabatan_id')->constrained('master_jabatan');
            $table->foreignId('bidang_id')->constrained('master_bidang');
            $table->foreignId('sub_bidang_id')->nullable()->constrained('master_sub');
            $table->string('email')->unique()->comment('Email unik untuk notifikasi');
            $table->string('password')->comment('Hashed password bcrypt/argon2');
            $table->string('no_telepon', 20)->nullable()->comment('Nomor telepon untuk SMS/WhatsApp');
            $table->string('pangkat', 50)->nullable()->comment('Pangkat PNS: Penata, Pembina, dll');
            $table->string('golongan', 10)->nullable()->comment('Golongan: III/a, III/b, IV/a, dll');
            $table->string('gelar_depan', 20)->nullable()->comment('Dr., Ir., Drs., dll');
            $table->string('gelar_belakang', 20)->nullable()->comment('S.T., M.T., M.Si., dll');
            $table->date('tanggal_lahir')->nullable()->comment('Untuk perhitungan usia dan pensiun');
            $table->enum('jenis_kelamin', ['L', 'P'])->comment('L atau P');
            $table->text('alamat')->nullable()->comment('Alamat lengkap');
            $table->enum('status_kepegawaian', ['PNS', 'PPPK', 'Kontrak'])->comment('PNS, PPPK, Kontrak');
            $table->enum('status_aktif', ['Aktif', 'Nonaktif', 'Cuti', 'Pensiun'])->default('Aktif')->comment('Aktif, Nonaktif, Cuti, Pensiun');
            $table->date('tanggal_masuk')->nullable()->comment('Tanggal mulai bekerja');
            $table->date('tanggal_keluar')->nullable()->comment('Tanggal berhenti, NULL jika masih aktif');
            $table->foreignId('atasan_langsung_id')->nullable()->constrained('master_pegawai')->comment('Self-reference untuk hierarchy');
            $table->string('foto_profile_path')->nullable()->comment('Path file foto profil');
            $table->timestamp('last_login_at')->nullable()->comment('Timestamp login terakhir untuk audit');
            $table->string('last_login_ip', 45)->nullable()->comment('IP address login terakhir');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('bidang_id');
            $table->index('jabatan_id');
            $table->index('status_aktif');
            $table->index('atasan_langsung_id');
        });

        // Tanda Tangan Digital
        Schema::create('master_ttd_digital', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('master_pegawai');
            $table->string('file_path')->comment('Path file gambar TTD PNG transparent');
            $table->string('file_hash')->comment('SHA256 hash untuk integrity check');
            $table->integer('image_width')->nullable()->comment('Lebar gambar dalam pixel');
            $table->integer('image_height')->nullable()->comment('Tinggi gambar dalam pixel');
            $table->date('valid_from')->comment('Tanggal mulai berlaku');
            $table->date('valid_until')->nullable()->comment('Tanggal expired, NULL=permanent');
            $table->boolean('is_active')->default(false)->comment('TRUE=aktif, hanya 1 aktif per pegawai');
            $table->boolean('is_verified')->default(false)->comment('TRUE jika sudah diverifikasi admin');
            $table->timestamps();
            $table->softDeletes();

            // Hanya satu TTD aktif per pegawai
            $table->unique(['pegawai_id', 'is_active'], 'uq_pegawai_active_ttd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_ttd_digital');
        Schema::dropIfExists('master_pegawai');
        Schema::dropIfExists('master_bidang');
        Schema::dropIfExists('master_jabatan');
    }
};

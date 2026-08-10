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
        Schema::table('user_profiles', function (Blueprint $table) {
            // Kolom baru berdasarkan DUK (Daftar Urut Kepangkatan) BAPPEDA — lihat
            // docs/DUK..BEZZETING BAPPEDA PER. MARET 2026 (1).csv — supaya data ini bisa
            // ikut diisi lewat import CSV pegawai (App\Services\PegawaiCsvImporter).
            $table->string('tempat_lahir')->nullable()->after('tanggal_lahir');
            $table->string('agama')->nullable()->after('jenis_kelamin');
            $table->date('tmt_cpns')->nullable()->after('tanggal_masuk');
            $table->date('tmt_pns')->nullable()->after('tmt_cpns');
            $table->date('tmt_golongan')->nullable()->after('golongan');
            $table->string('eselon')->nullable()->after('jabatan_id');
            $table->string('pendidikan_terakhir')->nullable()->after('eselon');
            $table->string('jenjang_pendidikan')->nullable()->after('pendidikan_terakhir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'tempat_lahir', 'agama', 'tmt_cpns', 'tmt_pns', 'tmt_golongan',
                'eselon', 'pendidikan_terakhir', 'jenjang_pendidikan',
            ]);
        });
    }
};

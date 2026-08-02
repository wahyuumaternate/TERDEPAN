<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('knj_penugasan', function (Blueprint $table) {
            $table->enum('prioritas', ['rendah', 'sedang', 'tinggi'])->nullable()->after('jenis');
            $table->decimal('nilai_awal', 6, 2)->nullable()->after('realisasi_persen')
                ->comment('bobot_persen x realisasi_persen, sebelum dipotong keterlambatan');
            $table->decimal('persentase_terlambat', 5, 2)->nullable()->after('nilai_awal')
                ->comment('0/5/10/15/20, dihitung dari deadline_terbaru vs tanggal_diselesaikan');
            $table->date('deadline_terbaru')->nullable()->after('tanggal_selesai')
                ->comment('Dipakai untuk seluruh perhitungan keterlambatan (aturan E9), diperbarui oleh perpanjangan waktu/revisi');
            $table->timestamp('tanggal_diselesaikan')->nullable()->after('deadline_terbaru')
                ->comment('Dicatat saat pegawai mengajukan Selesai');
        });

        // Ganti enum status: pending,dikerjakan,validasi,revisi,selesai -> pending,proses,revisi,terlambat,selesai,ditolak
        DB::statement('ALTER TABLE knj_penugasan DROP CONSTRAINT IF EXISTS knj_penugasan_status_check');
        DB::statement("ALTER TABLE knj_penugasan ADD CONSTRAINT knj_penugasan_status_check CHECK (status::text = ANY (ARRAY['pending','proses','revisi','terlambat','selesai','ditolak']))");

        DB::table('knj_penugasan')->where('status', 'dikerjakan')->update(['status' => 'proses']);
        DB::table('knj_penugasan')->where('status', 'validasi')->update(['status' => 'selesai']);
        DB::table('knj_penugasan')->update(['deadline_terbaru' => DB::raw('tanggal_selesai')]);
        DB::table('knj_penugasan')->whereNotNull('validated_at')->update(['tanggal_diselesaikan' => DB::raw('validated_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('knj_penugasan')->where('status', 'proses')->update(['status' => 'dikerjakan']);
        DB::table('knj_penugasan')->where('status', 'terlambat')->update(['status' => 'dikerjakan']);
        DB::table('knj_penugasan')->where('status', 'ditolak')->update(['status' => 'pending']);

        DB::statement('ALTER TABLE knj_penugasan DROP CONSTRAINT IF EXISTS knj_penugasan_status_check');
        DB::statement("ALTER TABLE knj_penugasan ADD CONSTRAINT knj_penugasan_status_check CHECK (status::text = ANY (ARRAY['pending','dikerjakan','validasi','revisi','selesai']))");

        Schema::table('knj_penugasan', function (Blueprint $table) {
            $table->dropColumn(['prioritas', 'nilai_awal', 'persentase_terlambat', 'deadline_terbaru', 'tanggal_diselesaikan']);
        });
    }
};

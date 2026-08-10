<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Default kolom FALSE (bukan TRUE) dengan sengaja — jalur pembuatan pegawai yang
        // sesungguhnya (MasterPegawaiController::store, PegawaiCsvImporter) sudah eksplisit
        // set true sendiri, jadi default kolom di sini murni fallback untuk baris yang dibuat
        // tanpa lewat jalur itu (mis. fixture test) — default true di sini akan memaksa RATUSAN
        // test existing yang bikin User::create()/factory() langsung ikut ter-redirect oleh
        // EnsurePasswordIsChanged tanpa mereka minta itu.
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('password');
        });

        // Backfill data lama: hanya user yang password-nya masih persis default 'password'
        // (dibuat lewat seeder) yang dipaksa ganti — user yang sudah punya password custom
        // dari MasterPegawaiController tidak perlu diganggu.
        DB::table('users')->select('id', 'password')->orderBy('id')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                DB::table('users')->where('id', $user->id)->update([
                    'must_change_password' => Hash::check('password', $user->password),
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });
    }
};

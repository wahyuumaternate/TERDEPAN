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
        Schema::table('knj_penugasan', function (Blueprint $table) {
            $table->uuid('grup_id')->nullable()->after('id')->index()
                ->comment('NULL = tugas biasa (satu pegawai). Terisi = bagian dari penugasan multi-pegawai');
            $table->enum('mode_grup', ['kolektif', 'per_orang'])->nullable()->after('grup_id');
            $table->boolean('is_koordinator')->default(false)->after('mode_grup')
                ->comment('Hanya relevan saat mode_grup = kolektif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('knj_penugasan', function (Blueprint $table) {
            $table->dropColumn(['grup_id', 'mode_grup', 'is_koordinator']);
        });
    }
};

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
            // Judul jabatan literal dari sumber data asli (mis. DUK: "PENELAAH TEKNIS
            // KEBIJAKAN"), disimpan terpisah dari jabatan_id — yang tetap menunjuk ke
            // salah satu dari 8 kode jabatan struktural sistem (menentukan hak akses).
            $table->string('jabatan_asli')->nullable()->after('jabatan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn('jabatan_asli');
        });
    }
};

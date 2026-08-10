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
        Schema::table('td_folders', function (Blueprint $table) {
            // Dibutuhkan supaya scopeInSubBidang() berfungsi (sebelumnya kolom ini dipakai
            // di kode tapi tidak pernah ada di skema) dan supaya hak akses KASUBAG bisa
            // dibatasi sampai level sub bidang, bukan cuma bidang.
            $table->foreignId('sub_bidang_id')->nullable()->after('bidang_id')
                ->constrained('master_sub')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('td_folders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sub_bidang_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * disk dicatat per baris (bukan diasumsikan dari config) supaya file yang sudah
     * tersimpan tetap bisa diakses dengan benar walau FILESYSTEM_DISK default aplikasi
     * berubah di kemudian hari (docs/plan/09-audit-storage.md).
     */
    public function up(): void
    {
        Schema::table('td_files', function (Blueprint $table) {
            $table->string('disk')->nullable()->default('local')->after('storage_path');
        });

        DB::table('td_files')->whereNull('disk')->update(['disk' => 'local']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('td_files', function (Blueprint $table) {
            $table->dropColumn('disk');
        });
    }
};

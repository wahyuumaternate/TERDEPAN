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
            $table->boolean('is_mandiri')->default(false)->after('pemberi_tugas_id')
                ->comment('Penanda permanen tugas dibuat mandiri, lepas dari nullability pemberi_tugas_id');
        });

        // Data lama: pemberi_tugas_id NULL berarti mandiri (satu-satunya jalan di kode lama)
        DB::table('knj_penugasan')->whereNull('pemberi_tugas_id')->update(['is_mandiri' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('knj_penugasan', function (Blueprint $table) {
            $table->dropColumn('is_mandiri');
        });
    }
};

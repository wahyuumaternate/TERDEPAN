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
            $table->timestamp('deadline_reminder_sent_at')->nullable()->after('deadline_terbaru');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('knj_penugasan', function (Blueprint $table) {
            $table->dropColumn('deadline_reminder_sent_at');
        });
    }
};

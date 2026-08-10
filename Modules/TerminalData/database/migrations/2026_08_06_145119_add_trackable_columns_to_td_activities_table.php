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
        Schema::table('td_activities', function (Blueprint $table) {
            $table->string('trackable_type')->nullable()->after('id');
            $table->string('trackable_id')->nullable()->after('trackable_type');
            $table->string('action')->after('trackable_id');
            $table->foreignId('user_id')->nullable()->after('action')->constrained('users')->nullOnDelete();
            $table->string('description')->nullable()->after('user_id');
            $table->json('metadata')->nullable()->after('description');

            $table->index(['trackable_type', 'trackable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('td_activities', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['trackable_type', 'trackable_id']);
            $table->dropColumn(['trackable_type', 'trackable_id', 'action', 'user_id', 'description', 'metadata']);
        });
    }
};

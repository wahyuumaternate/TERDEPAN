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
        Schema::create('knj_foto_bukti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_id')->constrained('knj_progress');
            $table->string('file_path')->comment('Path: storage/bukti/2024/10/...');
            $table->string('file_name')->comment('Original filename');
            $table->integer('file_size_kb')->comment('Ukuran dalam KB, max 5MB');
            $table->string('mime_type')->comment('image/jpeg, image/png only');
            $table->integer('urutan')->default(1)->comment('Urutan foto jika multiple');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['progress_id', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_foto_bukti');
    }
};

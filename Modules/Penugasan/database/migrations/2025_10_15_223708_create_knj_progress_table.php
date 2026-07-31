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
        Schema::create('knj_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('tipe_progress')->comment('TugasPokok, TugasHarian, TugasTambahan');
            $table->foreignUuid('tipe_progress_id');

            // Who and When
            $table->foreignId('pegawai_id')->constrained('users');
            $table->date('tanggal')->comment('Tanggal progress');

            // Progress Details
            $table->decimal('progress_persen', 5, 2)
                ->comment('Cumulative progress 0-100%');
            $table->text('deskripsi_kegiatan')
                ->comment('Apa yang dikerjakan');
            $table->text('kendala')->nullable()
                ->comment('Kendala yang dihadapi');

            // File attachments via polymorphic relation to td_files (handled in td_files.attachable_*)
            // No direct foreign key needed - files will reference this table via polymorphic

            $table->timestamps();

            $table->index(['pegawai_id', 'tanggal']);
            $table->index(['tipe_progress', 'tipe_progress_id']);
            $table->unique(['tipe_progress', 'tipe_progress_id', 'tanggal'], 'unique_daily_progress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_progress');
    }
};

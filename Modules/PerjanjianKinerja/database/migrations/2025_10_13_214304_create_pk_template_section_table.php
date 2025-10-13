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
        Schema::create('pk_template_section', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('pk_template');
            $table->string('section_code', 30)->comment('Kode: SASARAN, INDIKATOR, PROGRAM');
            $table->string('section_name', 50)->comment('Nama section untuk display');
            $table->enum('section_type', ['static', 'dynamic', 'table', 'repeatable'])->default('static');
            $table->text('content_template')->comment('HTML template section');
            $table->integer('urutan')->comment('Urutan section dalam dokumen 1,2,3...');
            $table->boolean('is_required')->default(true)->comment('TRUE=wajib, FALSE=optional');
            $table->timestamps();
            
            $table->unique(['template_id', 'section_code']);
            $table->index(['template_id', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pk_template_section');
    }
};

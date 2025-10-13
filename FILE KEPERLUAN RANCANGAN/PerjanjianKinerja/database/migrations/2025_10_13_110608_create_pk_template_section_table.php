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
            $table->unsignedBigInteger('template_id')->comment('Foreign key ke PK_TEMPLATE');
            $table->string('section_code', 50)->comment('Kode: SASARAN, INDIKATOR, PROGRAM');
            $table->string('section_name')->comment('Nama section untuk display');
            $table->enum('section_type', ['static', 'dynamic', 'table', 'repeatable'])->comment('Tipe section');
            $table->text('content_template')->nullable()->comment('HTML template section');
            $table->integer('urutan')->comment('Urutan section dalam dokumen 1,2,3...');
            $table->boolean('is_required')->default(false)->comment('TRUE=wajib, FALSE=optional');
            $table->timestamps();

            // Foreign keys
            $table->foreign('template_id')->references('id')->on('pk_template')->onDelete('cascade');

            // Unique constraint
            $table->unique(['template_id', 'section_code']);

            // Indexes
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

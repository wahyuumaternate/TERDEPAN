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
        Schema::create('knj_nilai_tahunan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Period & Employee
            $table->foreignId('pegawai_id')->constrained('master_pegawai');
            $table->year('tahun');
            
            // Calculated from monthly scores
            $table->decimal('rata_rata_bulanan', 5, 2)->default(0)
                ->comment('Average of 12 months');
            $table->decimal('nilai_tahunan', 5, 2)->default(0)
                ->comment('Final yearly score');
            $table->enum('kategori_nilai', [
                'Sangat_Baik',
                'Baik',
                'Cukup',
                'Kurang',
                'Sangat_Kurang'
            ])->nullable();
            
            // Summary & Recommendation
            $table->text('ringkasan_kinerja')->nullable()
                ->comment('Ringkasan kinerja setahun');
            $table->text('rekomendasi')->nullable()
                ->comment('Rekomendasi untuk tahun depan');
            
            // Finalization
            $table->boolean('is_finalized')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('master_pegawai');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            
            // Monthly breakdown (for reference)
            $table->json('breakdown_bulanan')->nullable()
                ->comment('Array of 12 monthly scores');
            
            $table->timestamps();
            
            // Constraints
            $table->unique(['pegawai_id', 'tahun'], 'unique_yearly_score');
            $table->index('kategori_nilai');
            $table->index(['is_finalized', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_nilai_tahunan');
    }
};

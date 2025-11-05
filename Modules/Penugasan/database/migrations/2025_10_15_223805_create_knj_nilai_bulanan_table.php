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
        Schema::create('knj_nilai_bulanan', function (Blueprint $table) {
            $table->uuid()->primary();
            
            // Period & Employee
            $table->foreignId('pegawai_id')->constrained('master_pegawai');
            $table->year('tahun');
            $table->tinyInteger('bulan')->comment('1-12');
            
            // Scores (calculated from tasks)
            $table->decimal('nilai_tugas_pokok', 5, 2)->default(0)
                ->comment('60-70% weight');
            $table->decimal('nilai_tugas_harian', 5, 2)->default(0)
                ->comment('20-30% weight');
            $table->decimal('nilai_tugas_tambahan', 5, 2)->default(0)
                ->comment('Bonus max 20%');
            
            // Adjustments
            $table->decimal('total_penalty', 5, 2)->default(0);
            $table->decimal('total_bonus', 5, 2)->default(0);
            
            // Final Score
            $table->decimal('nilai_total', 5, 2)->default(0)
                ->comment('Final score 0-120 (with bonus)');
            $table->enum('kategori_nilai', [
                'Sangat_Baik',    // > 90
                'Baik',           // 80-90
                'Cukup',          // 70-80
                'Kurang',         // 60-70
                'Sangat_Kurang'   // < 60
            ])->nullable();
            
            // Approval
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_finalized')->default(false)
                ->comment('TRUE = locked, cannot be changed');
            $table->foreignId('approved_by')->nullable()->constrained('master_pegawai');
            $table->text('catatan_atasan')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            
            // Calculation Metadata (for audit trail)
            $table->json('breakdown')->nullable()
                ->comment('Detail perhitungan nilai');
            
            $table->timestamps();
            
            // Constraints
            $table->unique(['pegawai_id', 'tahun', 'bulan'], 'unique_monthly_score');
            $table->index('kategori_nilai');
            $table->index(['is_approved', 'is_finalized']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knj_nilai_bulanan');
    }
};

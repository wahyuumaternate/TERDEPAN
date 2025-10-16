<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Penugasan\Database\Factories\IndikatorTugasFactory;

class IndikatorTugas extends Model
{
    use HasFactory;

    protected $table = 'knj_indikator_tugas';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tugas_pokok_id',
        'nama_indikator',
        'satuan',
        'target',
        'realisasi',
    ];

    protected $casts = [
        'target' => 'decimal:2',
        'realisasi' => 'decimal:2',
    ];

    protected $attributes = [
        'realisasi' => 0,
    ];

    // Relationships
    public function tugasPokok(): BelongsTo
    {
        return $this->belongsTo(TugasPokok::class, 'tugas_pokok_id');
    }

    // protected static function newFactory(): IndikatorTugasFactory
    // {
    //     // return IndikatorTugasFactory::new();
    // }
}

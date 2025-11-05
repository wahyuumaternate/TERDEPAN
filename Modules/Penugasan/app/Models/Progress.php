<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
// use Modules\Penugasan\Database\Factories\ProgressFactory;

class Progress extends Model
{
    use HasFactory;

    protected $table = 'knj_progress';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tipe_progress',
        'tipe_progress_id',
        'pegawai_id',
        'tanggal',
        'progress_persen',
        'deskripsi_kegiatan',
        'kendala',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'progress_persen' => 'decimal:2',
    ];

    // Relationships
    
    /**
     * Get the parent progressable model (TugasPokok, TugasHarian, TugasTambahan)
     */
    public function progressable(): MorphTo
    {
        return $this->morphTo('progressable', 'tipe_progress', 'tipe_progress_id');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'pegawai_id');
    }

    /**
     * Get all attached files from Terminal Data (polymorphic)
     */
    public function attachedFiles(): MorphMany
    {
        return $this->morphMany(\Modules\TerminalData\Models\TdFile::class, 'attachable');
    }

    // Scopes
    public function scopeByPegawai($query, $pegawaiId)
    {
        return $query->where('pegawai_id', $pegawaiId);
    }

    public function scopeByTanggal($query, $tanggal)
    {
        return $query->where('tanggal', $tanggal);
    }

    public function scopeByProgressable($query, $type, $id)
    {
        return $query->where('tipe_progress', $type)
                    ->where('tipe_progress_id', $id);
    }

    // protected static function newFactory(): ProgressFactory
    // {
    //     // return ProgressFactory::new();
    // }
}

<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Modules\Penugasan\Database\Factories\ProgressFactory;

class Progress extends Model
{
    use HasFactory;

    protected $table = 'knj_progress';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tugas_pokok_id',
        'tugas_harian_id',
        'tugas_tambahan_id',
        'penugasan_mandiri_id',
        'pegawai_id',
        'tanggal',
        'progress_persen',
        'deskripsi_kegiatan',
        'kendala',
        'dokumen_bukti_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'progress_persen' => 'decimal:2',
    ];

    // Relationships
    public function tugasPokok(): BelongsTo
    {
        return $this->belongsTo(TugasPokok::class, 'tugas_pokok_id');
    }

    public function tugasHarian(): BelongsTo
    {
        return $this->belongsTo(TugasHarian::class, 'tugas_harian_id');
    }

    public function tugasTambahan(): BelongsTo
    {
        return $this->belongsTo(TugasTambahan::class, 'tugas_tambahan_id');
    }

    public function penugasanMandiri(): BelongsTo
    {
        return $this->belongsTo(PenugasanMandiri::class, 'penugasan_mandiri_id');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'pegawai_id');
    }

    public function dokumenBukti(): BelongsTo
    {
        return $this->belongsTo(\Modules\Dokumen\Models\Dokumen::class, 'dokumen_bukti_id');
    }

    public function fotoBukti(): HasMany
    {
        return $this->hasMany(FotoBukti::class, 'progress_id');
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

    public function scopeByTugasPokok($query, $tugasPokokId)
    {
        return $query->where('tugas_pokok_id', $tugasPokokId);
    }

    // protected static function newFactory(): ProgressFactory
    // {
    //     // return ProgressFactory::new();
    // }
}

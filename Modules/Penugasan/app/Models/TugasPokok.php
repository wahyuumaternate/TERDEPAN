<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\PerjanjianKinerja\Models\PkPerjanjianKinerja;

// use Modules\Penugasan\Database\Factories\TugasPokokFactory;

class TugasPokok extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'knj_tugas_pokok';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'perjanjian_kinerja_id',
        'pegawai_id',
        'pemberi_tugas_id',
        'nama_tugas',
        'deskripsi',
        'bobot_persen',
        'periode_mulai',
        'periode_selesai',
        'target_value',
        'satuan',
        'status',
        'progress_persen',
        'diterima_at',
        'dokumen_lampiran_id',
    ];

    protected $casts = [
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
        'diterima_at' => 'datetime',
        'bobot_persen' => 'decimal:2',
        'progress_persen' => 'decimal:2',
        'target_value' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'Pending',
        'progress_persen' => 0,
    ];

    // Relationships
    public function perjanjianKinerja(): BelongsTo
    {
        return $this->belongsTo(PkPerjanjianKinerja::class, 'perjanjian_kinerja_id');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'pegawai_id');
    }

    public function pemberiTugas(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'pemberi_tugas_id');
    }

    public function dokumenLampiran(): BelongsTo
    {
        return $this->belongsTo(\Modules\Dokumen\Models\Dokumen::class, 'dokumen_lampiran_id');
    }

    public function tugasHarian(): HasMany
    {
        return $this->hasMany(TugasHarian::class, 'tugas_pokok_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class, 'tugas_pokok_id');
    }

    public function validasi(): HasOne
    {
        return $this->hasOne(Validasi::class, 'tugas_pokok_id');
    }

    public function delegasi(): HasOne
    {
        return $this->hasOne(Delegasi::class, 'tugas_pokok_id');
    }

    public function penugasanMandiri(): HasMany
    {
        return $this->hasMany(PenugasanMandiri::class, 'tugas_pokok_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['Diterima', 'Dikerjakan']);
    }

    public function scopeByPegawai($query, $pegawaiId)
    {
        return $query->where('pegawai_id', $pegawaiId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // protected static function newFactory(): TugasPokokFactory
    // {
    //     // return TugasPokokFactory::new();
    // }
}

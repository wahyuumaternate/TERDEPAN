<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
// use Modules\Penugasan\Database\Factories\TugasHarianFactory;

class TugasHarian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'knj_tugas_harian';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tugas_pokok_id',
        'pegawai_id',
        'pemberi_tugas_id',
        'nama_tugas',
        'deskripsi',
        'periode_type',
        'tanggal_mulai',
        'deadline',
        'target_penilaian',
        'penilaian',
        'nilai_akhir',
        'tanggal_penilaian',
        'target_value',
        'satuan',
        'status',
        'validasi_oleh',
        'tanggal_validasi',
        'catatan_validasi',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'deadline' => 'date',
        'tanggal_penilaian' => 'date',
        'tanggal_validasi' => 'date',
        'target_penilaian' => 'decimal:2',
        'penilaian' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
        'target_value' => 'decimal:2',
    ];

    protected $attributes = [
        'periode_type' => 'Harian',
        'status' => 'pending',
    ];

    // Relationships
    public function tugasPokok(): BelongsTo
    {
        return $this->belongsTo(TugasPokok::class, 'tugas_pokok_id');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'pegawai_id');
    }

    public function pemberiTugas(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'pemberi_tugas_id');
    }

    public function validasiOleh(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'validasi_oleh');
    }

    /**
     * Get all attached files from Terminal Data (polymorphic)
     */
    public function attachedFiles(): MorphMany
    {
        return $this->morphMany(\Modules\TerminalData\Models\TdFile::class, 'attachable');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class, 'tugas_harian_id');
    }

    public function validasi(): HasOne
    {
        return $this->hasOne(Validasi::class, 'tugas_harian_id');
    }

    public function delegasi(): HasOne
    {
        return $this->hasOne(Delegasi::class, 'tugas_harian_id');
    }

    public function historyRevisi(): HasMany
    {
        return $this->hasMany(HistoriRevisi::class, 'tugas_harian_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'dikerjakan']);
    }

    public function scopeByPegawai($query, $pegawaiId)
    {
        return $query->where('pegawai_id', $pegawaiId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // protected static function newFactory(): TugasHarianFactory
    // {
    //     // return TugasHarianFactory::new();
    // }
}

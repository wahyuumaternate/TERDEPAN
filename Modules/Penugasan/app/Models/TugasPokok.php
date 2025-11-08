<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\PerjanjianKinerja\Models\PkIndikator;
use Modules\PerjanjianKinerja\Models\PkPerjanjianKinerja;
use Illuminate\Support\Str;

// use Modules\Penugasan\Database\Factories\TugasPokokFactory;

class TugasPokok extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'knj_tugas_pokok';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pegawai_id',
        'perjanjian_kinerja_id',
        'indikator_id',
        'nama_tugas',
        'deskripsi',
        'bobot_persen',
        'target_value',
        'satuan',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'progress_persen',
        'nilai_akhir',
        'diterima_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'diterima_at' => 'datetime',
        'bobot_persen' => 'decimal:2',
        'progress_persen' => 'decimal:2',
        'target_value' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'pending',
        'progress_persen' => 0,
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // Status constants (sama dengan migrasi)
    public const STATUS_PENDING = 'pending';
    public const STATUS_DIKERJAKAN = 'dikerjakan';
    public const STATUS_SELESAI = 'selesai';
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_DIKERJAKAN,
        self::STATUS_SELESAI,
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

    public function indikatorPK(): BelongsTo
    {
        return $this->belongsTo(PkIndikator::class, 'indikator_id');
    }

    /**
     * Get all attached files from Terminal Data (polymorphic)
     */
    public function attachedFiles(): MorphMany
    {
        return $this->morphMany(\Modules\TerminalData\Models\TdFile::class, 'attachable');
    }

    public function tugasHarian(): HasMany
    {
        return $this->hasMany(TugasHarian::class, 'tugas_pokok_id');
    }

    public function progress(): MorphMany
    {
        return $this->morphMany(Progress::class, 'progressable', 'tipe_progress', 'tipe_progress_id');
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

    // protected static function newFactory(): TugasPokokFactory
    // {
    //     // return TugasPokokFactory::new();
    // }
}

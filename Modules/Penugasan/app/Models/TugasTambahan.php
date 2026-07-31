<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

// use Modules\Penugasan\Database\Factories\TugasTambahanFactory;

class TugasTambahan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'knj_tugas_tambahan';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pegawai_id',
        'pemberi_tugas_id',
        'nama_tugas',
        'deskripsi',
        'alasan_penugasan',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'validator_id',
        'hasil_validasi',
        'catatan_validasi',
        'penilaian_kualitas',
        'validated_at',
        'target_penilaian',
        'nilai_akhir',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'validated_at' => 'datetime',
        'target_penilaian' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
        'penilaian_kualitas' => 'integer',
    ];

    protected $attributes = [
        'status' => 'pending',
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

    // Status constants (sesuai migration - Bahasa Indonesia)
    public const STATUS_PENDING = 'pending';

    public const STATUS_DIKERJAKAN = 'dikerjakan';

    public const STATUS_VALIDASI = 'validasi';

    public const STATUS_REVISI = 'revisi';

    public const STATUS_SELESAI = 'selesai';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_DIKERJAKAN,
        self::STATUS_VALIDASI,
        self::STATUS_REVISI,
        self::STATUS_SELESAI,
    ];

    // Hasil validasi constants
    public const VALIDASI_DITERIMA = 'diterima';

    public const VALIDASI_REVISI = 'revisi';

    public const VALIDASI_DITOLAK = 'ditolak';

    // Relationships
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'pegawai_id');
    }

    public function pemberiTugas(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'pemberi_tugas_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'validator_id');
    }

    /**
     * Get all attached files from Terminal Data (polymorphic)
     */
    public function attachedFiles(): MorphMany
    {
        return $this->morphMany(\Modules\TerminalData\Models\TdFile::class, 'attachable');
    }

    /**
     * Get all progress entries (polymorphic)
     */
    public function progress(): MorphMany
    {
        return $this->morphMany(Progress::class, 'progressable', 'tipe_progress', 'tipe_progress_id');
    }

    /**
     * Get revision history (polymorphic)
     */
    public function historyRevisi(): MorphMany
    {
        return $this->morphMany(HistoriRevisi::class, 'revisable', 'tipe_revisi', 'tipe_revisi_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_DIKERJAKAN]);
    }

    public function scopeByPegawai($query, $pegawaiId)
    {
        return $query->where('pegawai_id', $pegawaiId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // protected static function newFactory(): TugasTambahanFactory
    // {
    //     // return TugasTambahanFactory::new();
    // }
}

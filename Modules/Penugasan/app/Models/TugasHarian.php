<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

// use Modules\Penugasan\Database\Factories\TugasHarianFactory;

class TugasHarian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'knj_tugas_harian';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tugas_pokok_id',
        'pegawai_id',
        'pemberi_tugas_id',
        'is_mandiri',
        'status_approval',
        'alasan_reject',
        'nama_tugas',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'target_value',
        'satuan',
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
        'is_mandiri' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'validated_at' => 'datetime',
        'target_penilaian' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
        'target_value' => 'decimal:2',
        'penilaian_kualitas' => 'integer',
    ];

    protected $attributes = [
        'is_mandiri' => false,
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

    // Approval status constants (untuk tugas mandiri)
    public const APPROVAL_PENDING = 'pending';

    public const APPROVAL_DITERIMA = 'diterima';

    public const APPROVAL_DITOLAK = 'ditolak';

    // Hasil validasi constants
    public const VALIDASI_DITERIMA = 'diterima';

    public const VALIDASI_REVISI = 'revisi';

    public const VALIDASI_DITOLAK = 'ditolak';

    // Relationships
    public function tugasPokok(): BelongsTo
    {
        return $this->belongsTo(TugasPokok::class, 'tugas_pokok_id');
    }

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

    public function scopeMandiri($query)
    {
        return $query->where('is_mandiri', true);
    }

    public function scopeNonMandiri($query)
    {
        return $query->where('is_mandiri', false);
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

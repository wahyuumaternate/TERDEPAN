<?php

namespace Modules\Penugasan\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Modules\Penugasan\Database\Factories\HistoriRevisiFactory;

class HistoriRevisi extends Model
{
    use HasFactory;

    protected static function newFactory(): HistoriRevisiFactory
    {
        return HistoriRevisiFactory::new();
    }

    protected $table = 'knj_histori_revisi';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'penugasan_id',
        'revisi_ke',
        'tanggal_revisi',
        'catatan_revisi',
        'deadline_revisi',
        'is_terlambat',
        'penalty_nilai',
        'direvisi_oleh',
        'pegawai_id',
        'submitted_at',
        'status',
    ];

    protected $casts = [
        'tanggal_revisi' => 'datetime',
        'deadline_revisi' => 'datetime',
        'submitted_at' => 'datetime',
        'revisi_ke' => 'integer',
        'is_terlambat' => 'boolean',
        'penalty_nilai' => 'decimal:2',
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

    // Status constants
    public const STATUS_PENDING = 'pending';

    public const STATUS_DIKIRIM = 'dikirim';

    public const STATUS_DITERIMA = 'diterima';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_DIKIRIM,
        self::STATUS_DITERIMA,
    ];

    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(Penugasan::class, 'penugasan_id');
    }

    /**
     * Relasi ke pembuat revisi (atasan)
     */
    public function direvisiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'direvisi_oleh');
    }

    /**
     * Relasi ke pegawai yang merevisi
     */
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pegawai_id');
    }

    /**
     * Get all attached files from Terminal Data (polymorphic)
     */
    public function attachedFiles(): MorphMany
    {
        return $this->morphMany(\Modules\TerminalData\Models\TdFile::class, 'attachable');
    }
}

<?php

namespace Modules\Penugasan\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Audit trail penolakan tugas (solo maupun grup kolektif). Dicatat sebelum status/hapus
 * ter-cascade, supaya riwayat penolakan tetap tertelusuri walau record Penugasan yang
 * bersangkutan nanti benar-benar di-soft-delete oleh job terjadwal setelah masa tenggang.
 */
class RiwayatPenolakan extends Model
{
    protected $table = 'knj_riwayat_penolakan';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'grup_id',
        'penugasan_ids',
        'ditolak_oleh',
        'alasan_reject',
        'ditolak_pada',
        'dibatalkan_pada',
        'dieksekusi_pada',
    ];

    protected $casts = [
        'penugasan_ids' => 'array',
        'ditolak_pada' => 'datetime',
        'dibatalkan_pada' => 'datetime',
        'dieksekusi_pada' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function ditolakOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditolak_oleh');
    }
}

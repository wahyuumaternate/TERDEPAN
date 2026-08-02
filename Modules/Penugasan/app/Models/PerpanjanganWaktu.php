<?php

namespace Modules\Penugasan\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PerpanjanganWaktu extends Model
{
    use HasFactory;

    protected $table = 'knj_perpanjangan_waktu';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'penugasan_id',
        'pegawai_id',
        'deadline_lama',
        'deadline_diminta',
        'deadline_disetujui',
        'alasan_pengajuan',
        'catatan_atasan',
        'status',
        'disetujui_oleh_id',
        'ke_berapa',
    ];

    protected $casts = [
        'deadline_lama' => 'date',
        'deadline_diminta' => 'date',
        'deadline_disetujui' => 'date',
        'ke_berapa' => 'integer',
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

    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_DISETUJUI = 'disetujui';

    public const STATUS_DITOLAK = 'ditolak';

    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(Penugasan::class, 'penugasan_id');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pegawai_id');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh_id');
    }
}

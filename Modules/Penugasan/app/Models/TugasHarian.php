<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'bobot_persen',
        'target_value',
        'satuan',
        'status',
        'dokumen_lampiran_id',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'deadline' => 'date',
        'bobot_persen' => 'decimal:2',
        'target_value' => 'decimal:2',
    ];

    protected $attributes = [
        'periode_type' => 'Harian',
        'status' => 'Assigned',
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

    public function dokumenLampiran(): BelongsTo
    {
        return $this->belongsTo(\Modules\Dokumen\Models\Dokumen::class, 'dokumen_lampiran_id');
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['Assigned', 'In_Progress']);
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

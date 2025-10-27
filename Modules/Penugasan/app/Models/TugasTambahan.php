<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
// use Modules\Penugasan\Database\Factories\TugasTambahanFactory;

class TugasTambahan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'knj_tugas_tambahan';

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
        'deadline',
        'target_penilaian',
        'penilaian',
        'nilai_akhir',
        'tanggal_penilaian',
        'status',
        'validasi_oleh',
        'tanggal_validasi',
        'catatan_validasi',
        'dokumen_lampiran_id',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'deadline' => 'date',
        'tanggal_penilaian' => 'date',
        'tanggal_validasi' => 'date',
        'target_penilaian' => 'decimal:2',
        'penilaian' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    // Relationships
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

    public function dokumenLampiran(): BelongsTo
    {
        return $this->belongsTo(\Modules\Dokumen\Models\Dokumen::class, 'dokumen_lampiran_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class, 'tugas_tambahan_id');
    }

    public function validasi(): HasOne
    {
        return $this->hasOne(Validasi::class, 'tugas_tambahan_id');
    }

    public function delegasi(): HasOne
    {
        return $this->hasOne(Delegasi::class, 'tugas_tambahan_id');
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

    // protected static function newFactory(): TugasTambahanFactory
    // {
    //     // return TugasTambahanFactory::new();
    // }
}

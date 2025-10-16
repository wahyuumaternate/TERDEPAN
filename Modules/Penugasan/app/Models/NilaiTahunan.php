<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Modules\Penugasan\Database\Factories\NilaiTahunanFactory;

class NilaiTahunan extends Model
{
    use HasFactory;

    protected $table = 'knj_nilai_tahunan';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pegawai_id',
        'tahun',
        'nilai_kinerja',
        'kategori_nilai',
        'catatan_penilaian',
        'penilai_id',
        'tanggal_penilaian',
        'status_penilaian',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'nilai_kinerja' => 'decimal:2',
        'tanggal_penilaian' => 'date',
    ];

    protected $attributes = [
        'status_penilaian' => 'Draft',
    ];

    // Relationships
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'pegawai_id');
    }

    public function penilai(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'penilai_id');
    }

    public function nilaiBulanan(): HasMany
    {
        return $this->hasMany(NilaiBulanan::class, 'pegawai_id', 'pegawai_id')
                    ->where('tahun', $this->tahun);
    }

    // Scopes
    public function scopeByPegawai($query, $pegawaiId)
    {
        return $query->where('pegawai_id', $pegawaiId);
    }

    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeApproved($query)
    {
        return $query->where('status_penilaian', 'Approved');
    }

    // protected static function newFactory(): NilaiTahunanFactory
    // {
    //     // return NilaiTahunanFactory::new();
    // }
}

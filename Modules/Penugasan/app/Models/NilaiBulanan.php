<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Penugasan\Database\Factories\NilaiBulananFactory;

class NilaiBulanan extends Model
{
    use HasFactory;

    protected $table = 'knj_nilai_bulanan';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pegawai_id',
        'tahun',
        'bulan',
        'nilai_kinerja',
        'kategori_nilai',
        'catatan_penilaian',
        'penilai_id',
        'tanggal_penilaian',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
        'nilai_kinerja' => 'decimal:2',
        'tanggal_penilaian' => 'date',
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

    // Scopes
    public function scopeByPegawai($query, $pegawaiId)
    {
        return $query->where('pegawai_id', $pegawaiId);
    }

    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeByBulan($query, $bulan)
    {
        return $query->where('bulan', $bulan);
    }

    // protected static function newFactory(): NilaiBulananFactory
    // {
    //     // return NilaiBulananFactory::new();
    // }
}

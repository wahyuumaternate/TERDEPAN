<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Penugasan\Database\Factories\WorkloadFactory;

class Workload extends Model
{
    use HasFactory;

    protected $table = 'knj_workload';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pegawai_id',
        'tahun',
        'bulan',
        'total_tugas_pokok',
        'total_tugas_harian',
        'total_tugas_tambahan',
        'total_penugasan_mandiri',
        'total_bobot',
        'kapasitas_maksimal',
        'persentase_beban',
        'status_beban',
        'catatan_workload',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
        'total_tugas_pokok' => 'integer',
        'total_tugas_harian' => 'integer',
        'total_tugas_tambahan' => 'integer',
        'total_penugasan_mandiri' => 'integer',
        'total_bobot' => 'decimal:2',
        'kapasitas_maksimal' => 'decimal:2',
        'persentase_beban' => 'decimal:2',
    ];

    // Relationships
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'pegawai_id');
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

    public function scopeOverloaded($query)
    {
        return $query->where('status_beban', 'Overloaded');
    }

    public function scopeUnderloaded($query)
    {
        return $query->where('status_beban', 'Underloaded');
    }

    // protected static function newFactory(): WorkloadFactory
    // {
    //     // return WorkloadFactory::new();
    // }
}

<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\MasterPegawai;

class CatatanMonitoring extends Model
{
    use HasFactory;

    protected $table = 'knj_catatan_monitoring';

    protected $fillable = [
        'pegawai_id',
        'tugas_id',
        'tugas_type',
        'catatan_oleh',
        'tanggal_catatan',
        'jenis_catatan',
        'isi_catatan'
    ];

    protected $casts = [
        'tanggal_catatan' => 'datetime',
    ];

    /**
     * Relasi ke pegawai yang dicatat
     */
    public function pegawai()
    {
        return $this->belongsTo(MasterPegawai::class, 'pegawai_id');
    }

    /**
     * Relasi ke pembuat catatan
     */
    public function pembuat()
    {
        return $this->belongsTo(MasterPegawai::class, 'catatan_oleh');
    }

    /**
     * Relasi ke tugas pokok
     */
    public function tugasPokok()
    {
        return $this->belongsTo(TugasPokok::class, 'tugas_id')->where('tugas_type', 'tugas_pokok');
    }

    /**
     * Relasi ke tugas harian
     */
    public function tugasHarian()
    {
        return $this->belongsTo(TugasHarian::class, 'tugas_id')->where('tugas_type', 'tugas_harian');
    }

    /**
     * Relasi ke tugas tambahan
     */
    public function tugasTambahan()
    {
        return $this->belongsTo(TugasTambahan::class, 'tugas_id')->where('tugas_type', 'tugas_tambahan');
    }
}

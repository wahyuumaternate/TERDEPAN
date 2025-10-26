<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\MasterPegawai;

class HistoriRevisi extends Model
{
    use HasFactory;

    protected $table = 'knj_histori_revisi';

    protected $fillable = [
        'tugas_id',
        'tugas_type',
        'revisi_oleh',
        'tanggal_revisi',
        'field_diubah',
        'nilai_lama',
        'nilai_baru',
        'catatan_revisi'
    ];

    protected $casts = [
        'tanggal_revisi' => 'datetime',
    ];

    /**
     * Relasi ke pembuat revisi
     */
    public function pembuat()
    {
        return $this->belongsTo(MasterPegawai::class, 'revisi_oleh');
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

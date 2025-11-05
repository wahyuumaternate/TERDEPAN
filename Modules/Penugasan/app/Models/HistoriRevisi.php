<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\MasterPegawai;

class HistoriRevisi extends Model
{
    use HasFactory;

    protected $table = 'histori_revisi';

    protected $fillable = [
        'tugas_harian_id',
        'tugas_tambahan_id',
        'revisi_ke',
        'tanggal_revisi',
        'catatan_revisi',
        'direvisi_oleh',
    ];

    protected $casts = [
        'tanggal_revisi' => 'datetime',
        'revisi_ke' => 'integer',
    ];

    /**
     * Relasi ke pembuat revisi
     */
    public function direvisiOleh()
    {
        return $this->belongsTo(MasterPegawai::class, 'direvisi_oleh');
    }

    /**
     * Relasi ke tugas harian
     */
    public function tugasHarian()
    {
        return $this->belongsTo(TugasHarian::class, 'tugas_harian_id');
    }

    /**
     * Relasi ke tugas tambahan
     */
    public function tugasTambahan()
    {
        return $this->belongsTo(TugasTambahan::class, 'tugas_tambahan_id');
    }

    /**
     * Get all attached files from Terminal Data (polymorphic)
     */
    public function attachedFiles(): MorphMany
    {
        return $this->morphMany(\Modules\TerminalData\Models\TdFile::class, 'attachable');
    }
}

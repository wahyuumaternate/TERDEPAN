<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Penugasan\Database\Factories\DelegasiFactory;

class Delegasi extends Model
{
    use HasFactory;

    protected $table = 'knj_delegasi';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tugas_pokok_id',
        'tugas_harian_id',
        'tugas_tambahan_id',
        'penugasan_mandiri_id',
        'pegawai_asal_id',
        'pegawai_tujuan_id',
        'delegator_id',
        'tanggal_delegasi',
        'alasan_delegasi',
        'catatan_delegasi',
        'status_delegasi',
        'approved_at',
    ];

    protected $casts = [
        'tanggal_delegasi' => 'date',
        'approved_at' => 'datetime',
    ];

    protected $attributes = [
        'status_delegasi' => 'Pending',
    ];

    // Relationships
    public function tugasPokok(): BelongsTo
    {
        return $this->belongsTo(TugasPokok::class, 'tugas_pokok_id');
    }

    public function tugasHarian(): BelongsTo
    {
        return $this->belongsTo(TugasHarian::class, 'tugas_harian_id');
    }

    public function tugasTambahan(): BelongsTo
    {
        return $this->belongsTo(TugasTambahan::class, 'tugas_tambahan_id');
    }

    public function penugasanMandiri(): BelongsTo
    {
        return $this->belongsTo(PenugasanMandiri::class, 'penugasan_mandiri_id');
    }

    public function pegawaiAsal(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'pegawai_asal_id');
    }

    public function pegawaiTujuan(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'pegawai_tujuan_id');
    }

    public function delegator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'delegator_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status_delegasi', 'Pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status_delegasi', 'Approved');
    }

    public function scopeByDelegator($query, $delegatorId)
    {
        return $query->where('delegator_id', $delegatorId);
    }

    // protected static function newFactory(): DelegasiFactory
    // {
    //     // return DelegasiFactory::new();
    // }
}

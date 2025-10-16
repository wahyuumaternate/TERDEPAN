<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Penugasan\Database\Factories\RevisiFactory;

class Revisi extends Model
{
    use HasFactory;

    protected $table = 'knj_revisi';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tugas_pokok_id',
        'tugas_harian_id',
        'tugas_tambahan_id',
        'penugasan_mandiri_id',
        'pegawai_id',
        'revisor_id',
        'tanggal_revisi',
        'alasan_revisi',
        'catatan_revisi',
        'status_revisi',
    ];

    protected $casts = [
        'tanggal_revisi' => 'date',
    ];

    protected $attributes = [
        'status_revisi' => 'Pending',
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

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'pegawai_id');
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'revisor_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status_revisi', 'Pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status_revisi', 'Completed');
    }

    public function scopeByRevisor($query, $revisorId)
    {
        return $query->where('revisor_id', $revisorId);
    }

    // protected static function newFactory(): RevisiFactory
    // {
    //     // return RevisiFactory::new();
    // }
}

<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Penugasan\Database\Factories\ValidasiFactory;

class Validasi extends Model
{
    use HasFactory;

    protected $table = 'knj_validasi';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tugas_pokok_id',
        'tugas_harian_id',
        'tugas_tambahan_id',
        'penugasan_mandiri_id',
        'pegawai_id',
        'validator_id',
        'tanggal_validasi',
        'status_validasi',
        'catatan_validasi',
        'nilai_kinerja',
    ];

    protected $casts = [
        'tanggal_validasi' => 'date',
        'nilai_kinerja' => 'decimal:2',
    ];

    protected $attributes = [
        'status_validasi' => 'Pending',
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

    public function validator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'validator_id');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status_validasi', 'Approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status_validasi', 'Rejected');
    }

    public function scopeByValidator($query, $validatorId)
    {
        return $query->where('validator_id', $validatorId);
    }

    // protected static function newFactory(): ValidasiFactory
    // {
    //     // return ValidasiFactory::new();
    // }
}

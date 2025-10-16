<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
// use Modules\Penugasan\Database\Factories\PenugasanMandiriFactory;

class PenugasanMandiri extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'knj_penugasan_mandiri';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pegawai_id',
        'tugas_pokok_id',
        'nama_tugas',
        'deskripsi',
        'target_value',
        'satuan',
        'target_selesai',
        'status',
        'approved_by',
        'alasan_reject',
        'approved_at',
    ];

    protected $casts = [
        'target_selesai' => 'date',
        'approved_at' => 'datetime',
        'target_value' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'Pending_Approval',
    ];

    // Relationships
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'pegawai_id');
    }

    public function tugasPokok(): BelongsTo
    {
        return $this->belongsTo(TugasPokok::class, 'tugas_pokok_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'approved_by');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class, 'penugasan_mandiri_id');
    }

    public function validasi(): HasOne
    {
        return $this->hasOne(Validasi::class, 'penugasan_mandiri_id');
    }

    // Scopes
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'Pending_Approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }

    public function scopeByPegawai($query, $pegawaiId)
    {
        return $query->where('pegawai_id', $pegawaiId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // protected static function newFactory(): PenugasanMandiriFactory
    // {
    //     // return PenugasanMandiriFactory::new();
    // }
}

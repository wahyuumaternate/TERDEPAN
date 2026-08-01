<?php

namespace Modules\Penugasan\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Modules\Penugasan\Database\Factories\NilaiTahunanFactory;

class NilaiTahunan extends Model
{
    use HasFactory;

    protected $table = 'knj_nilai_tahunan';

    protected $fillable = [
        'pegawai_id',
        'tahun',
        'rata_rata_bulanan',
        'nilai_tahunan',
        'kategori_nilai',
        'ringkasan_kinerja',
        'rekomendasi',
        'is_finalized',
        'approved_by',
        'approved_at',
        'finalized_at',
        'breakdown_bulanan',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'rata_rata_bulanan' => 'decimal:2',
        'nilai_tahunan' => 'decimal:2',
        'is_finalized' => 'boolean',
        'approved_at' => 'datetime',
        'finalized_at' => 'datetime',
        'breakdown_bulanan' => 'array',
    ];

    // Relationships
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pegawai_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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

    public function scopeFinalized($query)
    {
        return $query->where('is_finalized', true);
    }
}

<?php

namespace Modules\Penugasan\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Modules\Penugasan\Database\Factories\NilaiBulananFactory;

class NilaiBulanan extends Model
{
    use HasFactory;

    protected $table = 'knj_nilai_bulanan';

    protected $fillable = [
        'pegawai_id',
        'tahun',
        'bulan',
        'total_bobot',
        'total_nilai',
        'total_penalty',
        'total_bonus',
        'nilai_total',
        'kategori_nilai',
        'is_approved',
        'is_finalized',
        'approved_by',
        'catatan_atasan',
        'approved_at',
        'finalized_at',
        'breakdown',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
        'total_bobot' => 'decimal:2',
        'total_nilai' => 'decimal:2',
        'total_penalty' => 'decimal:2',
        'total_bonus' => 'decimal:2',
        'nilai_total' => 'decimal:2',
        'is_approved' => 'boolean',
        'is_finalized' => 'boolean',
        'approved_at' => 'datetime',
        'finalized_at' => 'datetime',
        'breakdown' => 'array',
    ];

    // Kategori nilai constants
    public const KATEGORI_SANGAT_BAIK = 'Sangat_Baik';

    public const KATEGORI_BAIK = 'Baik';

    public const KATEGORI_CUKUP = 'Cukup';

    public const KATEGORI_KURANG = 'Kurang';

    public const KATEGORI_SANGAT_KURANG = 'Sangat_Kurang';

    /**
     * Tentukan kategori nilai berdasarkan nilai_total (setelah penalty/bonus)
     */
    public static function kategoriDariNilai(float $nilaiTotal): string
    {
        return match (true) {
            $nilaiTotal > 90 => self::KATEGORI_SANGAT_BAIK,
            $nilaiTotal >= 80 => self::KATEGORI_BAIK,
            $nilaiTotal >= 70 => self::KATEGORI_CUKUP,
            $nilaiTotal >= 60 => self::KATEGORI_KURANG,
            default => self::KATEGORI_SANGAT_KURANG,
        };
    }

    // Relationships
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pegawai_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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
}

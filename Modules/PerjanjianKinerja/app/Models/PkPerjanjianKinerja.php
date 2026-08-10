<?php

namespace Modules\PerjanjianKinerja\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PkPerjanjianKinerja extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pk_perjanjian_kinerja';

    protected $fillable = [
        'nomor_perjanjian',
        'pegawai_id',
        'atasan_id',
        'template_id',
        'periode_id',
        'tahun',
        'periode_mulai',
        'periode_selesai',
        'tempat_ttd',
        'tanggal_ttd',
        'total_anggaran',
        'status_dokumen',
        'status_validasi',
        'divalidasi_oleh',
        'divalidasi_pada',
        'catatan_validasi',
        'catatan',
        'is_locked',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
        'tanggal_ttd' => 'date',
        'divalidasi_pada' => 'datetime',
        'total_anggaran' => 'decimal:2',
        'is_locked' => 'boolean',
    ];

    public function pegawai()
    {
        return $this->belongsTo(User::class, 'pegawai_id');
    }

    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    public function periode()
    {
        return $this->belongsTo(PkPeriode::class, 'periode_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'divalidasi_oleh');
    }

    public function template()
    {
        return $this->belongsTo(PkTemplate::class, 'template_id');
    }

    public function sasaran()
    {
        return $this->hasMany(PkSasaran::class, 'perjanjian_kinerja_id')->orderBy('urutan');
    }

    public function dokumen()
    {
        return $this->hasMany(PkDokumen::class, 'perjanjian_kinerja_id');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status_dokumen', $status);
    }

    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function isEditable()
    {
        return ! $this->is_locked && in_array($this->status_dokumen, ['Draft', 'Generated']);
    }

    public function calculateTotalAnggaran()
    {
        $total = 0;
        foreach ($this->sasaran as $sasaran) {
            foreach ($sasaran->indikator as $indikator) {
                foreach ($indikator->program as $program) {
                    $total += $program->anggaran;
                }
            }
        }
        $this->total_anggaran = $total;
        $this->save();

        return $total;
    }

    public static function generateNomorPerjanjian($tahun, $bidangCode = 'BAPPEDA')
    {
        $lastNumber = static::where('tahun', $tahun)
            ->where('nomor_perjanjian', 'like', "PK/{$bidangCode}/{$tahun}/%")
            ->orderBy('nomor_perjanjian', 'desc')
            ->first();

        $sequence = $lastNumber ? intval(explode('/', $lastNumber->nomor_perjanjian)[3]) + 1 : 1;

        return sprintf('PK/%s/%s/%04d', $bidangCode, $tahun, $sequence);
    }
}

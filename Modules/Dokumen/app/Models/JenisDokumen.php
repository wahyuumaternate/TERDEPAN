<?php

namespace Modules\Dokumen\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JenisDokumen extends Model
{
    use HasFactory;

    protected $table = 'doc_jenis';

    protected $fillable = [
        'kategori_id',
        'kode',
        'nama',
        'folder_pattern',
        'nomor_format',
        'allowed_ext',
        'max_size_mb',
        'perlu_nomor'
    ];

    protected $casts = [
        'kategori_id' => 'integer',
        'max_size_mb' => 'integer',
        'perlu_nomor' => 'boolean',
    ];

    // Relationships
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function dokumen()
    {
        return $this->hasMany(Dokumen::class, 'jenis_id');
    }

    public function nomorCounter()
    {
        return $this->hasMany(NomorCounter::class, 'jenis_id');
    }

    // Scopes
    public function scopeByKategori($query, $kategoriId)
    {
        return $query->where('kategori_id', $kategoriId);
    }

    public function scopePerluNomor($query)
    {
        return $query->where('perlu_nomor', true);
    }

    // Accessors
    public function getAllowedExtensionsAttribute()
    {
        return explode(',', $this->allowed_ext);
    }

    public function getMaxSizeBytesAttribute()
    {
        return $this->max_size_mb * 1024 * 1024;
    }

    // Methods
    public function isExtensionAllowed($extension)
    {
        $allowed = $this->allowed_extensions;
        return in_array(strtolower($extension), array_map('strtolower', $allowed));
    }

    public function generateFolderPath($bidang, $year = null, $month = null)
    {
        $year = $year ?? date('Y');
        $month = $month ?? date('m');

        $path = $this->folder_pattern;
        $path = str_replace('{bidang}', $bidang, $path);
        $path = str_replace('{jenis}', $this->kode, $path);
        $path = str_replace('{year}', $year, $path);
        $path = str_replace('{month}', $month, $path);

        return $path;
    }

    public function getNextNomor($bidangId, $tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        $counter = NomorCounter::firstOrCreate(
            [
                'jenis_id' => $this->id,
                'bidang_id' => $bidangId,
                'tahun' => $tahun,
            ],
            ['counter' => 0]
        );

        $counter->increment('counter');
        $counter->refresh();

        return $counter->counter;
    }

    public function formatNomor($bidangKode, $tahun, $sequence)
    {
        $format = $this->nomor_format;

        $nomor = str_replace('{bidang}', $bidangKode, $format);
        $nomor = str_replace('{year}', $tahun, $nomor);
        $nomor = str_replace('{seq}', str_pad($sequence, 4, '0', STR_PAD_LEFT), $nomor);

        return $nomor;
    }
}

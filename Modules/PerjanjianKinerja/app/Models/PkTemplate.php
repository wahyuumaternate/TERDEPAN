<?php

namespace Modules\PerjanjianKinerja\Models;

use App\Models\MasterJabatan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PkTemplate extends Model
{
    use HasFactory;

    protected $table = 'pk_template';

    protected $fillable = [
        'kode_template',
        'nama_template',
        'jabatan_id',
        'tahun',
        'kop_surat_html',
        'header_template',
        'pernyataan_pembuka',
        'pernyataan_penutup',
        'footer_template',
        'page_size',
        'orientation',
        'versi',
        'is_active',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'versi' => 'integer',
        'is_active' => 'boolean',
    ];

    public function jabatan()
    {
        return $this->belongsTo(MasterJabatan::class, 'jabatan_id');
    }

    public function sections()
    {
        return $this->hasMany(PkTemplateSection::class, 'template_id')->orderBy('urutan');
    }

    public function perjanjianKinerja()
    {
        return $this->hasMany(PkPerjanjianKinerja::class, 'template_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeByJabatan($query, $jabatanId)
    {
        return $query->where('jabatan_id', $jabatanId);
    }

    public static function getActiveTemplate($jabatanId, $tahun)
    {
        return static::active()->byJabatan($jabatanId)->byTahun($tahun)->first();
    }

    public function activate()
    {
        static::byJabatan($this->jabatan_id)->byTahun($this->tahun)
            ->where('id', '!=', $this->id)->update(['is_active' => false]);
        $this->is_active = true;
        return $this->save();
    }

    public function canDelete()
    {
        return $this->perjanjianKinerja()->count() === 0;
    }
}

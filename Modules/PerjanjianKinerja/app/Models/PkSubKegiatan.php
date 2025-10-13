<?php

namespace Modules\PerjanjianKinerja\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PkSubKegiatan extends Model
{
    use HasFactory;

    protected $table = 'pk_sub_kegiatan';

    protected $fillable = [
        'kegiatan_id',
        'urutan',
        'kode_sub_kegiatan',
        'nama_sub_kegiatan',
        'anggaran',
        'target_value',
        'satuan',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'anggaran' => 'decimal:2',
        'target_value' => 'integer',
    ];

    public function kegiatan()
    {
        return $this->belongsTo(PkKegiatan::class, 'kegiatan_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }

    public static function getNextUrutan($kegiatanId)
    {
        $last = static::where('kegiatan_id', $kegiatanId)
            ->orderBy('urutan', 'desc')->first();
        return $last ? $last->urutan + 1 : 1;
    }

    public function getFormattedAnggaranAttribute()
    {
        return 'Rp ' . number_format($this->anggaran, 0, ',', '.');
    }

    public function getFormattedTargetAttribute()
    {
        return number_format($this->target_value, 0, ',', '.') . ' ' . $this->satuan;
    }
}

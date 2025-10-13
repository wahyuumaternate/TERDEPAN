<?php

namespace Modules\PerjanjianKinerja\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PkSasaran extends Model
{
    use HasFactory;

    protected $table = 'pk_sasaran';

    protected $fillable = [
        'perjanjian_kinerja_id', 'urutan', 'sasaran_strategis',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function perjanjianKinerja()
    {
        return $this->belongsTo(PkPerjanjianKinerja::class, 'perjanjian_kinerja_id');
    }

    public function indikator()
    {
        return $this->hasMany(PkIndikator::class, 'sasaran_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }

    public static function getNextUrutan($perjanjianKinerjaId)
    {
        $last = static::where('perjanjian_kinerja_id', $perjanjianKinerjaId)
            ->orderBy('urutan', 'desc')->first();
        return $last ? $last->urutan + 1 : 1;
    }
}


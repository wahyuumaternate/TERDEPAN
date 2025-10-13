<?php

namespace Modules\PerjanjianKinerja\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PkProgram extends Model
{
    use HasFactory;

    protected $table = 'pk_program';

    protected $fillable = [
        'indikator_id',
        'urutan',
        'kode_program',
        'nama_program',
        'anggaran',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'anggaran' => 'decimal:2',
    ];

    public function indikator()
    {
        return $this->belongsTo(PkIndikator::class, 'indikator_id');
    }

    public function kegiatan()
    {
        return $this->hasMany(PkKegiatan::class, 'program_id')->orderBy('urutan');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }

    public static function getNextUrutan($indikatorId)
    {
        $last = static::where('indikator_id', $indikatorId)
            ->orderBy('urutan', 'desc')->first();
        return $last ? $last->urutan + 1 : 1;
    }

    public function getFormattedAnggaranAttribute()
    {
        return 'Rp ' . number_format($this->anggaran, 0, ',', '.');
    }
}

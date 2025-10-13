<?php

namespace Modules\PerjanjianKinerja\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PkKegiatan extends Model
{
    use HasFactory;

    protected $table = 'pk_kegiatan';

    protected $fillable = [
        'program_id',
        'urutan',
        'kode_kegiatan',
        'nama_kegiatan',
        'anggaran',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'anggaran' => 'decimal:2',
    ];

    public function program()
    {
        return $this->belongsTo(PkProgram::class, 'program_id');
    }

    public function subKegiatan()
    {
        return $this->hasMany(PkSubKegiatan::class, 'kegiatan_id')->orderBy('urutan');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }

    public static function getNextUrutan($programId)
    {
        $last = static::where('program_id', $programId)
            ->orderBy('urutan', 'desc')->first();
        return $last ? $last->urutan + 1 : 1;
    }

    public function getFormattedAnggaranAttribute()
    {
        return 'Rp ' . number_format($this->anggaran, 0, ',', '.');
    }
}

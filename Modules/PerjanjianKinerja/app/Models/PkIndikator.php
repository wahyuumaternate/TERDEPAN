<?php

namespace Modules\PerjanjianKinerja\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PkIndikator extends Model
{
    use HasFactory;

    protected $table = 'pk_indikator';

    protected $fillable = [
        'sasaran_id',
        'indikator_sasaran',
        'satuan',
        'target_value',
        'keterangan',
    ];

    protected $casts = [
        'target_value' => 'int:10',
    ];

    public function sasaran()
    {
        return $this->belongsTo(PkSasaran::class, 'sasaran_id');
    }

    public function program()
    {
        return $this->hasMany(PkProgram::class, 'indikator_id')->orderBy('urutan');
    }

    public function getTotalAnggaranAttribute()
    {
        return $this->program->sum('anggaran');
    }
}

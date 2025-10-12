<?php

namespace Modules\Dokumen\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NomorCounter extends Model
{
    use HasFactory;

    protected $table = 'doc_nomor_counter';

    protected $fillable = [
        'jenis_id',
        'bidang_id',
        'tahun',
        'counter'
    ];

    protected $casts = [
        'tahun' => 'integer',
        'counter' => 'integer'
    ];

    // Relations
    public function jenis()
    {
        return $this->belongsTo(JenisDokumen::class, 'jenis_id');
    }

    public function bidang()
    {
        return $this->belongsTo(\App\Models\MasterBidang::class, 'bidang_id');
    }
}

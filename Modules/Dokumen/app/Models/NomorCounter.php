<?php

namespace Modules\Dokumen\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\MasterBidang;

class NomorCounter extends Model
{
    use HasFactory;

    protected $table = 'doc_nomor_counter';

    protected $fillable = [
        'bidang_id',
        'tahun',
        'counter'
    ];

    protected $casts = [
        'tahun' => 'integer',
        'counter' => 'integer'
    ];

    // Relations
    public function bidang()
    {
        return $this->belongsTo(MasterBidang::class, 'bidang_id');
    }
}

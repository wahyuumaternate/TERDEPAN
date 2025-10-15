<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Penugasan\Database\Factories\FotoBuktiFactory;

class FotoBukti extends Model
{
    use HasFactory;

    protected $table = 'knj_foto_bukti';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'progress_id',
        'nama_file',
        'path_file',
        'mime_type',
        'file_size',
        'keterangan',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    // Relationships
    public function progress(): BelongsTo
    {
        return $this->belongsTo(Progress::class, 'progress_id');
    }

    // protected static function newFactory(): FotoBuktiFactory
    // {
    //     // return FotoBuktiFactory::new();
    // }
}

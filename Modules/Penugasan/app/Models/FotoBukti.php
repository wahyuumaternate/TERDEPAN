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
        'file_path',
        'file_name',
        'file_size_kb',
        'mime_type',
        'urutan',
    ];

    protected $casts = [
        'file_size_kb' => 'integer',
        'urutan' => 'integer',
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

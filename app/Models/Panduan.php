<?php

namespace App\Models;

use App\Services\PanduanService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Panduan extends Model
{
    protected $guarded = [];

    protected $casts = [
        'size' => 'integer',
    ];

    public function diunggahOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diunggah_oleh_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (Panduan $panduan) {
            app(PanduanService::class)->deletePhysical($panduan->path, $panduan->disk);
        });
    }
}

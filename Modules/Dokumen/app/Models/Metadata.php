<?php

namespace Modules\Dokumen\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Metadata extends Model
{
    use HasFactory;

    protected $table = 'doc_metadata';

    protected $fillable = [
        'dokumen_id',
        'key',
        'value'
    ];

    protected $casts = [
        'dokumen_id' => 'integer',
    ];

    // Relationships
    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class, 'dokumen_id');
    }

    // Scopes
    public function scopeByKey($query, $key)
    {
        return $query->where('key', $key);
    }

    public function scopeByDokumen($query, $dokumenId)
    {
        return $query->where('dokumen_id', $dokumenId);
    }

    // Methods
    public static function setForDokumen($dokumenId, $key, $value)
    {
        return self::updateOrCreate(
            [
                'dokumen_id' => $dokumenId,
                'key' => $key
            ],
            ['value' => $value]
        );
    }

    public static function getForDokumen($dokumenId, $key, $default = null)
    {
        $meta = self::where('dokumen_id', $dokumenId)
            ->where('key', $key)
            ->first();

        return $meta ? $meta->value : $default;
    }

    public static function deleteForDokumen($dokumenId, $key)
    {
        return self::where('dokumen_id', $dokumenId)
            ->where('key', $key)
            ->delete();
    }
}

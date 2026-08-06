<?php

namespace Modules\Penugasan\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

/**
 * Pivot penugasan <-> td_files. Menggantikan pola lama TdFile.attachable_id (morph tunggal)
 * yang memaksa duplikasi baris TdFile untuk penugasan grup kolektif — lihat migration
 * create_knj_penugasan_eviden_table dan docs/analysis/rekomendasi-arsitektur-eviden-kinerja.md §2.1.
 */
class PenugasanEviden extends Pivot
{
    protected $table = 'knj_penugasan_eviden';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'penugasan_id',
        'td_file_id',
        'created_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}

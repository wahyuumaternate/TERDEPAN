<?php

namespace Modules\PerjanjianKinerja\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PkTemplateSection extends Model
{
    use HasFactory;

    protected $table = 'pk_template_section';

    protected $fillable = [
        'template_id',
        'section_code',
        'section_name',
        'section_type',
        'content_template',
        'urutan',
        'is_required',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'is_required' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(PkTemplate::class, 'template_id');
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }
}

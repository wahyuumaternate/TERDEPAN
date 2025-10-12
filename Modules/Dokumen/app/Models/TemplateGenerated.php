<?php

namespace Modules\Dokumen\Models;

use App\Models\MasterPegawai;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class TemplateGenerated extends Model
{
    use HasFactory;

    protected $table = 'doc_template_generated';

    protected $fillable = [
        'template_id',
        'dokumen_id',
        'user_id',
        'data_variables',
        'file_path',
        'generated_at',
    ];

    protected $casts = [
        'data_variables' => 'array',
        'generated_at' => 'datetime',
    ];

    // Relationships
    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class, 'dokumen_id');
    }

    public function user()
    {
        return $this->belongsTo(MasterPegawai::class, 'user_id');
    }
}

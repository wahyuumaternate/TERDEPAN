<?php

namespace Modules\Dokumen\Models;

use App\Models\MasterPegawai;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Template extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'doc_template';

    protected $fillable = [
        'nama',
        'kode',
        'deskripsi',
        'content',
        'variables',
        'file_template',
        'format_output',
        'is_active',
        'header',
        'footer',
        'settings',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'variables' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(MasterPegawai::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(MasterPegawai::class, 'updated_by');
    }

    public function generated()
    {
        return $this->hasMany(TemplateGenerated::class, 'template_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Methods
    public function getAvailableVariables()
    {
        return $this->variables ?? $this->extractVariablesFromContent();
    }

    private function extractVariablesFromContent()
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $this->content, $matches);
        return array_unique($matches[1] ?? []);
    }
}

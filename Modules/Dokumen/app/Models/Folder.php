<?php

namespace Modules\Dokumen\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    protected $table = 'doc_folder';

    protected $fillable = [
        'parent_id',
        'bidang_id',
        'nama',
        'path',
        'level',
        'is_auto',
        'total_files',
        'created_by',
    ];

    protected $casts = [
        'is_auto' => 'boolean',
        'level' => 'integer',
        'total_files' => 'integer',
    ];

    // Relations
    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function bidang()
    {
        return $this->belongsTo(\App\Models\MasterBidang::class, 'bidang_id');
    }

    public function dokumen()
    {
        return $this->hasMany(Dokumen::class, 'folder_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\MasterPegawai::class, 'created_by');
    }
}

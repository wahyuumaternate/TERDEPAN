<?php

namespace Modules\PerjanjianKinerja\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PkDokumen extends Model
{
    use HasFactory;

    protected $table = 'pk_dokumen';

    public $timestamps = false;

    protected $fillable = [
        'perjanjian_kinerja_id',
        'jenis_dokumen',
        'nomor_dokumen',
        'file_name',
        'file_path',
        'file_hash',
        'file_size_kb',
        'versi',
        'total_pages',
        'generated_by',
        'generated_at',
        'is_latest',
        'dokumen_id',
    ];

    protected $casts = [
        'versi' => 'integer',
        'total_pages' => 'integer',
        'file_size_kb' => 'integer',
        'is_latest' => 'boolean',
        'generated_at' => 'datetime',
    ];

    public function perjanjianKinerja()
    {
        return $this->belongsTo(PkPerjanjianKinerja::class, 'perjanjian_kinerja_id');
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function scopeLatest($query)
    {
        return $query->where('is_latest', true);
    }

    public function fileExists()
    {
        return Storage::exists($this->file_path);
    }

    public function download()
    {
        if ($this->fileExists()) {
            return Storage::download($this->file_path, $this->file_name);
        }
        throw new \Exception('File tidak ditemukan.');
    }
}

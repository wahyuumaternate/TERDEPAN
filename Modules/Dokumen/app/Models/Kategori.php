<?php

namespace Modules\Dokumen\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'doc_kategori';
    
    protected $fillable = [
        'nama',
        'icon',
        'warna',
        'urutan'
    ];
    
    protected $casts = [
        'urutan' => 'integer',
    ];
    
    // Relationships
    public function jenis()
    {
        return $this->hasMany(Jenis::class, 'kategori_id');
    }
    
    public function dokumen()
    {
        return $this->hasManyThrough(
            Dokumen::class,
            Jenis::class,
            'kategori_id', // Foreign key on Jenis table
            'jenis_id',    // Foreign key on Dokumen table
            'id',          // Local key on Kategori table
            'id'           // Local key on Jenis table
        );
    }
    
    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }
    
    // Accessors
    public function getIconHtmlAttribute()
    {
        return '<i class="' . $this->icon . '"></i>';
    }
    
    // Methods
    public function getTotalDokumen()
    {
        return $this->dokumen()->count();
    }
    
    public function getPersentaseDokumen()
    {
        $total = Dokumen::count();
        if ($total == 0) return 0;
        
        $jumlah = $this->getTotalDokumen();
        return round(($jumlah / $total) * 100, 1);
    }
}
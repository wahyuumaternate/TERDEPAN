<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class MasterPegawai extends Model
{
    Use HasRoles, Notifiable;
    
    protected $table = 'master_pegawai';
    protected $guarded = [];

    public function jabatan()
    {
        return $this->belongsTo(MasterJabatan::class, 'jabatan_id');
    }

    public function bidang()
    {
        return $this->belongsTo(MasterBidang::class, 'bidang_id');
    }

    public function atasanLangsung()
    {
        return $this->belongsTo(self::class, 'atasan_langsung_id');
    }

    public function bawahanLangsung()
    {
        return $this->hasMany(self::class, 'atasan_langsung_id');
    }

    public function ttdDigital()
    {
        return $this->hasMany(MasterTtdDigital::class, 'pegawai_id');
    }
}

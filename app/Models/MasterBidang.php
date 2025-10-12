<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterBidang extends Model
{
    protected $table = 'master_bidang';
    protected $guarded = ['id'];

    public function pegawai()
    {
        return $this->hasMany(MasterPegawai::class, 'bidang_id');
    }

    public function folder()
    {
        return $this->hasMany(\Modules\Dokumen\Models\Folder::class, 'bidang_id');
    }
}

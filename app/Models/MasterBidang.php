<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterBidang extends Model
{
    protected $table = 'master_bidang';
    protected $guarded = [];

    public function pegawai()
    {
        return $this->hasMany(MasterPegawai::class, 'bidang_id');
    }
}

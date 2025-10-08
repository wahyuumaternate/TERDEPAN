<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterTtdDigital extends Model
{
    protected $table = 'master_ttd_digital';
    protected $guarded = [];

    public function pegawai()
    {
        return $this->belongsTo(MasterPegawai::class, 'pegawai_id');
    }
}

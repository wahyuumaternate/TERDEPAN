<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterJabatan extends Model
{
    protected $table = 'master_jabatan';
    protected $guarded = [];

    public function pegawai()
    {
        return $this->hasMany(MasterPegawai::class, 'jabatan_id');
    }
}

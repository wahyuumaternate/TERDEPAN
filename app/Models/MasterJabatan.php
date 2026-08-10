<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterJabatan extends Model
{
    protected $table = 'master_jabatan';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_struktural' => 'boolean',
        'bebas_nilai_kinerja' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pegawai()
    {
        return $this->hasMany(UserProfile::class, 'jabatan_id');
    }
}

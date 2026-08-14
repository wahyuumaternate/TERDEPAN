<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterBidang extends Model
{
    protected $table = 'master_bidang';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pegawai()
    {
        return $this->hasMany(UserProfile::class, 'bidang_id');
    }

    public function subBidang()
    {
        return $this->hasMany(MasterSubBidang::class, 'bidang_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSubBidang extends Model
{
    protected $table = 'master_sub';
    protected $guarded = ['id'];

    protected $fillable = [
        'bidang_id',
        'nama',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

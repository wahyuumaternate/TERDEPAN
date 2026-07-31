<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_masuk' => 'date',
            'tanggal_keluar' => 'date',
            'last_login_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(MasterJabatan::class, 'jabatan_id');
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(MasterBidang::class, 'bidang_id');
    }

    public function subBidang(): BelongsTo
    {
        return $this->belongsTo(MasterSubBidang::class, 'sub_bidang_id');
    }

    public function atasanLangsung(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_langsung_id');
    }

    public function bawahanLangsung(): HasMany
    {
        return $this->hasMany(self::class, 'atasan_langsung_id', 'user_id');
    }
}

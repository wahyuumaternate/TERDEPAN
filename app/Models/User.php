<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the tugas pokok for the user.
     */
    public function tugasPokok()
    {
        return $this->hasMany(\Modules\Penugasan\Models\TugasPokok::class, 'pegawai_id');
    }

    /**
     * Get the tugas harian for the user.
     */
    public function tugasHarian()
    {
        return $this->hasMany(\Modules\Penugasan\Models\TugasHarian::class, 'pegawai_id');
    }

    /**
     * Get the tugas tambahan for the user.
     */
    public function tugasTambahan()
    {
        return $this->hasMany(\Modules\Penugasan\Models\TugasTambahan::class, 'pegawai_id');
    }
}

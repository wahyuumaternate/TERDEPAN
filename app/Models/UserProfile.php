<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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
            'tmt_cpns' => 'date',
            'tmt_pns' => 'date',
            'tmt_golongan' => 'date',
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

    /**
     * URL foto profil, disk-agnostic — satu-satunya tempat yang tahu cara membangun URL
     * dari foto_profile_path, supaya Blade tidak perlu asset('storage/'...) manual lagi
     * (docs/plan/09-audit-storage.md). Null (bukan error) kalau path kosong atau file-nya
     * sudah tidak ada di disk yang tercatat — termasuk foto lama era public_path() yang
     * fisiknya di luar jangkauan Storage disk 'public'.
     */
    public function getFotoProfileUrlAttribute(): ?string
    {
        if (! $this->foto_profile_path) {
            return null;
        }

        $disk = $this->disk ?? 'public';

        if (! Storage::disk($disk)->exists($this->foto_profile_path)) {
            return null;
        }

        return Storage::disk($disk)->url($this->foto_profile_path);
    }
}

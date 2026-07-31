<?php

namespace Modules\PerjanjianKinerja\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PkPeriode extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pk_periode';

    protected $fillable = [
        'tahun',
        'nama_periode',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'is_active',
        'dibuka_oleh',
        'dibuka_pada',
        'ditutup_oleh',
        'ditutup_pada',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
        'dibuka_pada' => 'datetime',
        'ditutup_pada' => 'datetime',
    ];

    /**
     * Relasi ke pegawai yang membuka periode
     */
    public function pembuka()
    {
        return $this->belongsTo(User::class, 'dibuka_oleh');
    }

    /**
     * Relasi ke pegawai yang menutup periode
     */
    public function penutup()
    {
        return $this->belongsTo(User::class, 'ditutup_oleh');
    }

    /**
     * Relasi ke perjanjian kinerja
     */
    public function perjanjianKinerja()
    {
        return $this->hasMany(PkPerjanjianKinerja::class, 'periode_id');
    }

    /**
     * Scope untuk periode aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('is_active', true)->where('status', 'Aktif');
    }

    /**
     * Scope untuk tahun tertentu
     */
    public function scopeTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    /**
     * Check apakah periode sedang aktif/dibuka
     */
    public function isDibuka()
    {
        return $this->is_active
            && $this->status === 'Aktif'
            && now()->between($this->tanggal_mulai, $this->tanggal_selesai);
    }

    /**
     * Check apakah periode sudah ditutup
     */
    public function isDitutup()
    {
        return $this->status === 'Ditutup' || ! $this->is_active;
    }

    /**
     * Check apakah periode sudah melewati deadline
     */
    public function isMelewatiDeadline()
    {
        return now()->greaterThan($this->tanggal_selesai);
    }

    /**
     * Get periode aktif untuk tahun berjalan
     */
    public static function getPeriodeAktif($tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        return self::where('tahun', $tahun)
            ->where('is_active', true)
            ->where('status', 'Aktif')
            ->first();
    }
}

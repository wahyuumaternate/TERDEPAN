<?php

namespace Modules\Penugasan\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Penugasan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'knj_penugasan';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pegawai_id',
        'pemberi_tugas_id',
        'is_mandiri',
        'grup_id',
        'mode_grup',
        'is_koordinator',
        'jenis',
        'prioritas',
        'nama_tugas',
        'deskripsi',
        'alasan_penugasan',
        'tanggal_mulai',
        'tanggal_selesai',
        'deadline_terbaru',
        'tanggal_diselesaikan',
        'target_value',
        'satuan',
        'bobot_persen',
        'progress_persen',
        'realisasi_persen',
        'nilai_awal',
        'persentase_terlambat',
        'nilai_akhir',
        'status',
        'status_approval',
        'alasan_reject',
        'validator_id',
        'hasil_validasi',
        'catatan_validasi',
        'validated_at',
        'diterima_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'deadline_terbaru' => 'date',
        'tanggal_diselesaikan' => 'datetime',
        'validated_at' => 'datetime',
        'diterima_at' => 'datetime',
        'target_value' => 'decimal:2',
        'bobot_persen' => 'decimal:2',
        'progress_persen' => 'decimal:2',
        'realisasi_persen' => 'decimal:2',
        'nilai_awal' => 'decimal:2',
        'persentase_terlambat' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
        'is_mandiri' => 'boolean',
        'is_koordinator' => 'boolean',
    ];

    protected $attributes = [
        'status' => 'pending',
        'progress_persen' => 0,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // Jenis constants
    public const JENIS_POKOK = 'pokok';

    public const JENIS_TAMBAHAN = 'tambahan';

    public const JENISES = [
        self::JENIS_POKOK,
        self::JENIS_TAMBAHAN,
    ];

    // Status constants (alur seragam untuk semua jenis)
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROSES = 'proses';

    public const STATUS_REVISI = 'revisi';

    public const STATUS_TERLAMBAT = 'terlambat';

    public const STATUS_SELESAI = 'selesai';

    public const STATUS_DITOLAK = 'ditolak';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROSES,
        self::STATUS_REVISI,
        self::STATUS_TERLAMBAT,
        self::STATUS_SELESAI,
        self::STATUS_DITOLAK,
    ];

    // Prioritas constants
    public const PRIORITAS_RENDAH = 'rendah';

    public const PRIORITAS_SEDANG = 'sedang';

    public const PRIORITAS_TINGGI = 'tinggi';

    public const PRIORITASES = [
        self::PRIORITAS_RENDAH,
        self::PRIORITAS_SEDANG,
        self::PRIORITAS_TINGGI,
    ];

    // Mode grup constants (penugasan multi-pegawai)
    public const MODE_GRUP_KOLEKTIF = 'kolektif';

    public const MODE_GRUP_PER_ORANG = 'per_orang';

    public const MODE_GRUPS = [
        self::MODE_GRUP_KOLEKTIF,
        self::MODE_GRUP_PER_ORANG,
    ];

    // Approval status constants (untuk tugas mandiri)
    public const APPROVAL_PENDING = 'pending';

    public const APPROVAL_DITERIMA = 'diterima';

    public const APPROVAL_DITOLAK = 'ditolak';

    // Hasil validasi constants
    public const VALIDASI_DITERIMA = 'diterima';

    public const VALIDASI_REVISI = 'revisi';

    public const VALIDASI_DITOLAK = 'ditolak';

    // Relationships
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pegawai_id');
    }

    public function pemberiTugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemberi_tugas_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validator_id');
    }

    /**
     * Get all attached files from Terminal Data (polymorphic)
     */
    public function attachedFiles(): MorphMany
    {
        return $this->morphMany(\Modules\TerminalData\Models\TdFile::class, 'attachable');
    }

    /**
     * Rincian aktivitas harian (dulu tabel Tugas Harian terpisah, kini log progress)
     */
    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class, 'penugasan_id');
    }

    /**
     * Riwayat revisi
     */
    public function historyRevisi(): HasMany
    {
        return $this->hasMany(HistoriRevisi::class, 'penugasan_id');
    }

    /**
     * Relasi ke penugasan induk lain dalam grup (multi-pegawai)
     */
    public function grupAnggota(): HasMany
    {
        return $this->hasMany(self::class, 'grup_id', 'grup_id')->where('id', '!=', $this->id);
    }

    public function perpanjanganWaktu(): HasMany
    {
        return $this->hasMany(PerpanjanganWaktu::class, 'penugasan_id');
    }

    /**
     * Nilai awal = Bobot (pecahan) x Realisasi, sebelum dipotong keterlambatan.
     */
    public function hitungNilaiAwal(): ?float
    {
        if ($this->bobot_persen === null || $this->realisasi_persen === null) {
            return null;
        }

        return round(((float) $this->bobot_persen * (float) $this->realisasi_persen) / 100, 2);
    }

    /**
     * Nilai akhir = Nilai awal x (1 - Persentase Terlambat).
     */
    public function hitungNilaiAkhir(float $nilaiAwal, float $persentaseTerlambat): float
    {
        return round($nilaiAwal * (1 - $persentaseTerlambat / 100), 2);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROSES]);
    }

    public function scopePokok($query)
    {
        return $query->where('jenis', self::JENIS_POKOK);
    }

    public function scopeTambahan($query)
    {
        return $query->where('jenis', self::JENIS_TAMBAHAN);
    }

    public function scopeMandiri($query)
    {
        return $query->where('is_mandiri', true);
    }

    public function scopeDitugaskan($query)
    {
        return $query->where('is_mandiri', false);
    }

    public function scopeByPegawai($query, $pegawaiId)
    {
        return $query->where('pegawai_id', $pegawaiId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}

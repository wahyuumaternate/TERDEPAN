<?php

namespace Modules\Dokumen\Models;

use App\Models\MasterPegawai;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Log extends Model
{
    use HasFactory;

    protected $table = 'doc_log';

    protected $fillable = [
        'dokumen_id',
        'user_id',
        'action',
        'ip_address'
    ];

    protected $casts = [
        'dokumen_id' => 'integer',
        'user_id' => 'integer',
    ];

    public $timestamps = true;
    const UPDATED_AT = null; // Only use created_at

    // Relationships
    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class, 'dokumen_id');
    }

    public function user()
    {
        return $this->belongsTo(MasterPegawai::class, 'user_id');
    }

    // Scopes
    public function scopeByDokumen($query, $dokumenId)
    {
        return $query->where('dokumen_id', $dokumenId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    // Accessors
    public function getActionBadgeAttribute()
    {
        return match ($this->action) {
            'View' => '<span class="badge bg-info">View</span>',
            'Download' => '<span class="badge bg-success">Download</span>',
            'Upload' => '<span class="badge bg-primary">Upload</span>',
            'Edit' => '<span class="badge bg-warning">Edit</span>',
            'Delete' => '<span class="badge bg-danger">Delete</span>',
            default => '<span class="badge bg-secondary">' . $this->action . '</span>'
        };
    }

    public function getFormattedTimeAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Methods
    public static function logActivity($dokumenId, $action, $userId = null, $ipAddress = null)
    {
        return self::create([
            'dokumen_id' => $dokumenId,
            'user_id' => $userId ?? Auth::user()->id,
            'action' => $action,
            'ip_address' => $ipAddress ?? request()->ip(),
        ]);
    }

    public static function getRecentActivity($limit = 10)
    {
        return self::with(['dokumen', 'user'])
            ->recent($limit)
            ->get();
    }

    public static function getActivityByUser($userId, $limit = 10)
    {
        return self::with('dokumen')
            ->byUser($userId)
            ->recent($limit)
            ->get();
    }
}

// ============================================================
// FILE: Modules/Dokumen/Entities/NomorCounter.php
// ============================================================

namespace Modules\Dokumen\Model;

use App\Models\MasterBidang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Inti\Entities\Bidang;

class NomorCounter extends Model
{
    use HasFactory;

    protected $table = 'doc_nomor_counter';

    protected $fillable = [
        'jenis_id',
        'bidang_id',
        'tahun',
        'counter'
    ];

    protected $casts = [
        'jenis_id' => 'integer',
        'bidang_id' => 'integer',
        'tahun' => 'integer',
        'counter' => 'integer',
    ];

    // Relationships
    public function bidang()
    {
        return $this->belongsTo(MasterBidang::class, 'bidang_id');
    }

    // Scopes
    public function scopeByJenis($query, $jenisId)
    {
        return $query->where('jenis_id', $jenisId);
    }

    public function scopeByBidang($query, $bidangId)
    {
        return $query->where('bidang_id', $bidangId);
    }

    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeCurrentYear($query)
    {
        return $query->where('tahun', date('Y'));
    }

    // Methods
    public static function getNextCounter($jenisId, $bidangId, $tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        $counter = self::firstOrCreate(
            [
                'jenis_id' => $jenisId,
                'bidang_id' => $bidangId,
                'tahun' => $tahun,
            ],
            ['counter' => 0]
        );

        $counter->increment('counter');
        $counter->refresh();

        return $counter->counter;
    }

    public static function getCurrentCounter($jenisId, $bidangId, $tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        $counter = self::where([
            'jenis_id' => $jenisId,
            'bidang_id' => $bidangId,
            'tahun' => $tahun,
        ])->first();

        return $counter ? $counter->counter : 0;
    }

    public static function resetCounter($jenisId, $bidangId, $tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        return self::where([
            'jenis_id' => $jenisId,
            'bidang_id' => $bidangId,
            'tahun' => $tahun,
        ])->update(['counter' => 0]);
    }

    public static function resetAllCounters($tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        return self::where('tahun', $tahun)
            ->update(['counter' => 0]);
    }
}

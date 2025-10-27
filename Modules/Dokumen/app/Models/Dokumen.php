<?php


namespace Modules\Dokumen\Models;

use App\Models\MasterPegawai;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Dokumen extends Model
{
    use HasFactory;

    protected $table = 'doc_dokumen';

    protected $fillable = [
        'nomor',
        'folder_id',
        'judul',
        'deskripsi',
        'tanggal_dokumen',
        'nomor_surat',
        'status',
        'version',
        'views',
        'downloads',
        'uploaded_by',
        'metadata', // JSON field untuk data fleksibel
        'related_type', // Polymorphic type
        'related_id', // Polymorphic id
        'is_public',
    ];

    protected $casts = [
        'folder_id' => 'integer',
        'tanggal_dokumen' => 'date',
        'version' => 'integer',
        'views' => 'integer',
        'downloads' => 'integer',
        'uploaded_by' => 'integer',
        'metadata' => 'array',
        'is_public' => 'boolean',
    ];

    protected $appends = ['status_badge'];

    // Relationships
    public function folder()
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    public function uploader()
    {
        return $this->belongsTo(MasterPegawai::class, 'uploaded_by');
    }

    // Polymorphic relation untuk attach ke tugas, PK, dll
    public function related()
    {
        return $this->morphTo();
    }

    public function files()
    {
        return $this->hasMany(File::class, 'dokumen_id');
    }

    public function currentFile()
    {
        return $this->hasOne(File::class, 'dokumen_id')
            ->where('is_current', true)
            ->latest('version');
    }

    public function metadata()
    {
        return $this->hasMany(Metadata::class, 'dokumen_id');
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'dokumen_id');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'Draft');
    }

    public function scopeFinal($query)
    {
        return $query->where('status', 'Final');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'Archived');
    }

    public function scopeByFolder($query, $folderId)
    {
        return $query->where('folder_id', $folderId);
    }

    public function scopeByTanggal($query, $from, $to)
    {
        return $query->whereBetween('tanggal_dokumen', [$from, $to]);
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('judul', 'like', "%{$keyword}%")
                ->orWhere('nomor', 'like', "%{$keyword}%")
                ->orWhere('nomor_surat', 'like', "%{$keyword}%")
                ->orWhere('deskripsi', 'like', "%{$keyword}%");
        });
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->orderBy('views', 'desc')->limit($limit);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'Draft' => '<span class="badge bg-warning">Draft</span>',
            'Final' => '<span class="badge bg-success">Final</span>',
            'Archived' => '<span class="badge bg-secondary">Archived</span>',
            default => '<span class="badge bg-light">Unknown</span>'
        };
    }

    public function getFormattedSizeAttribute()
    {
        $file = $this->currentFile;
        if (!$file) return '0 KB';

        $sizeKb = $file->size_kb;
        $units = ['KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($sizeKb >= 1024 && $unitIndex < count($units) - 1) {
            $sizeKb /= 1024;
            $unitIndex++;
        }

        return number_format($sizeKb, 2) . ' ' . $units[$unitIndex];
    }

    public function getFullUrlAttribute()
    {
        $file = $this->currentFile;
        if (!$file) return null;

        return asset('storage/' . $file->file_path);
    }

    // Methods
    public function incrementViews()
    {
        $this->increment('views');
    }

    public function incrementDownloads()
    {
        $this->increment('downloads');
    }

    public function getMetadataValue($key, $default = null)
    {
        $meta = $this->metadata()->where('key', $key)->first();
        return $meta ? $meta->value : $default;
    }

    public function setMetadata($key, $value)
    {
        return $this->metadata()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public function logActivity($action, $userId = null, $ipAddress = null)
    {
        return $this->logs()->create([
            'user_id' => $userId ?? Auth::user()?->id,
            'action' => $action,
            'ip_address' => $ipAddress ?? request()->ip(),
        ]);
    }

    public function canBeDeleted()
    {
        // Add your business logic here
        // For example: only draft documents can be deleted
        return $this->status === 'Draft';
    }

    public function canBeEdited()
    {
        return in_array($this->status, ['Draft', 'Final']);
    }

    public function archive()
    {
        return $this->update(['status' => 'Archived']);
    }

    public function finalize()
    {
        return $this->update(['status' => 'Final']);
    }

    public function isDraft()
    {
        return $this->status === 'Draft';
    }

    public function isFinal()
    {
        return $this->status === 'Final';
    }

    public function isArchived()
    {
        return $this->status === 'Archived';
    }
}

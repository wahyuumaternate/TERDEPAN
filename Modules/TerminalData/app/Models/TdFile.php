<?php

namespace Modules\TerminalData\Models;

use App\Models\MasterBidang;
use App\Models\MasterSubBidang;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\TerminalData\Database\Factories\TdFileFactory;

class TdFile extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Satu sumber kebenaran untuk daftar ekstensi gambar — dipakai isImage() (per-instance)
     * maupun query filter langsung (mis. laporan bulanan eviden foto), supaya tidak ada dua
     * daftar hardcoded terpisah yang bisa saling berbeda seiring waktu.
     */
    public const EXTENSI_GAMBAR = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

    protected $table = 'td_files';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'folder_id',
        'bidang_id',
        'sub_bidang_id',
        'name',
        'original_name',
        'description',
        'mime_type',
        'extension',
        'size',
        'storage_path',
        'disk',
        'hash',
        'thumbnail_path',
        'document_number',
        'document_date',
        'document_type',
        'status',
        'version',
        'original_file_id',
        'is_latest_version',
        'version_notes',
        'attachable_type',
        'attachable_id',
        'is_public',
        'is_starred',
        'is_locked',
        'is_encrypted',
        'metadata',
        'extracted_content',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'extracted_content' => 'array',
        'document_date' => 'date',
        'is_latest_version' => 'boolean',
        'is_public' => 'boolean',
        'is_starred' => 'boolean',
        'is_locked' => 'boolean',
        'is_encrypted' => 'boolean',
        'last_viewed_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'views' => 'integer',
        'downloads' => 'integer',
        'version' => 'integer',
        'size' => 'integer',
    ];

    protected $with = ['creator', 'folder'];

    protected static function newFactory()
    {
        return TdFileFactory::new();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            // Generate hash if not exists — disk-agnostic (bukan Storage::path(), yang
            // rusak di disk non-lokal). Jalur ini tidak pernah tereksekusi lewat flow
            // upload manapun sekarang (semua pemanggil TdFile::create() sudah kirim
            // hash), tapi tetap dibuat aman kalau ada pemanggil baru di masa depan.
            if (empty($model->hash) && $model->storage_path) {
                $disk = $model->disk ?? config('filesystems.default');
                $model->hash = hash('sha256', Storage::disk($disk)->get($model->storage_path));
            }
        });

        static::created(function ($model) {
            // Update folder stats
            if ($model->folder) {
                $model->folder->updateStats();
            }
        });

        static::deleting(function ($model) {
            // Hapus file fisik HANYA saat force delete — model ini pakai SoftDeletes,
            // dan hook `deleting` ikut fire saat soft delete (pindah ke sampah) juga.
            // Sebelumnya file fisik langsung terhapus permanen begitu dipindah ke
            // sampah, padahal restore() cuma mengembalikan baris DB — hasilnya record
            // yang di-restore menunjuk ke file yang sudah tidak ada.
            if (! $model->isForceDeleting()) {
                return;
            }

            $disk = $model->disk ?? config('filesystems.default');
            app(\Modules\TerminalData\Services\FileManagerService::class)->deletePhysical($model->storage_path, $disk);
            app(\Modules\TerminalData\Services\FileManagerService::class)->deletePhysical($model->thumbnail_path, $disk);
        });

        static::deleted(function ($model) {
            // Update folder stats
            if ($model->folder) {
                $model->folder->updateStats();
            }
        });
    }

    // ==================== Relationships ====================

    public function folder()
    {
        return $this->belongsTo(TdFolder::class, 'folder_id');
    }

    public function originalFile()
    {
        return $this->belongsTo(TdFile::class, 'original_file_id');
    }

    public function versions()
    {
        return $this->hasMany(TdFile::class, 'original_file_id')
            ->orderBy('version', 'desc');
    }

    public function attachable()
    {
        return $this->morphTo();
    }

    /**
     * Penugasan yang mereferensikan file ini sebagai eviden kinerja (lewat pivot
     * knj_penugasan_eviden — lihat Penugasan::eviden() untuk sisi sebaliknya).
     */
    public function penugasan()
    {
        return $this->belongsToMany(\Modules\Penugasan\Models\Penugasan::class, 'knj_penugasan_eviden', 'td_file_id', 'penugasan_id')
            ->withPivot('created_by')
            ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function bidang()
    {
        return $this->belongsTo(MasterBidang::class);
    }

    public function subBidang()
    {
        return $this->belongsTo(MasterSubBidang::class, 'sub_bidang_id');
    }

    public function shares()
    {
        return $this->morphMany(TdShare::class, 'shareable');
    }

    public function sharedWith()
    {
        return $this->belongsToMany(
            User::class,
            'td_file_shares',
            'file_id',
            'pegawai_id'
        )->withPivot('access_level', 'expires_at')->withTimestamps();
    }

    public function activities()
    {
        return $this->morphMany(TdActivity::class, 'trackable');
    }

    public function tags()
    {
        return $this->morphToMany(TdTag::class, 'taggable', 'td_taggables');
    }

    public function comments()
    {
        return $this->morphMany(TdComment::class, 'commentable');
    }

    public function lock()
    {
        return $this->hasOne(TdFileLock::class, 'file_id');
    }

    // ==================== Scopes ====================

    public function scopeInFolder($query, $folderId)
    {
        return $query->where('folder_id', $folderId);
    }

    public function scopeLatestVersions($query)
    {
        return $query->where('is_latest_version', true);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeStarred($query)
    {
        return $query->where('is_starred', true);
    }

    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('original_name', 'like', "%{$search}%")
                ->orWhere('document_number', 'like', "%{$search}%");
        });
    }

    public function scopeRecentlyUploaded($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeInBidang($query, $bidangId)
    {
        return $query->where('bidang_id', $bidangId);
    }

    public function scopeInSubBidang($query, $subBidangId)
    {
        return $query->where('sub_bidang_id', $subBidangId);
    }

    /**
     * Scope untuk filter file berdasarkan akses user
     * Digunakan untuk permission-based filtering
     */
    public function scopeForUser($query, User $user)
    {
        // Semua user yang terautentikasi bisa melihat semua file
        return $query;
    }

    /**
     * Scope untuk file yang bisa diedit oleh user
     */
    public function scopeEditableBy($query, User $user)
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - edit semua
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return $query;
        }

        // KABID - edit file bidangnya
        if ($kodeJabatan === 'KABID') {
            return $query->where(function ($q) use ($user) {
                $q->where('bidang_id', $user->profile?->bidang_id)
                    ->orWhereHas('folder', function ($fq) use ($user) {
                        $fq->where('bidang_id', $user->profile?->bidang_id);
                    });
            });
        }

        // KASUBAG, PELAKSANA, JAFUNG, GATEK - edit file sendiri
        if (in_array($kodeJabatan, ['KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])) {
            return $query->where('created_by', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Scope untuk file yang bisa dihapus oleh user
     */
    public function scopeDeletableBy($query, User $user)
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - delete semua
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return $query;
        }

        // KABID - delete file bidangnya
        if ($kodeJabatan === 'KABID') {
            return $query->where(function ($q) use ($user) {
                $q->where('bidang_id', $user->profile?->bidang_id)
                    ->orWhereHas('folder', function ($fq) use ($user) {
                        $fq->where('bidang_id', $user->profile?->bidang_id);
                    });
            });
        }

        // KASUBAG, PELAKSANA, JAFUNG, GATEK - delete file sendiri
        if (in_array($kodeJabatan, ['KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])) {
            return $query->where('created_by', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Scope untuk file yang bisa didownload oleh user
     */
    public function scopeDownloadableBy($query, User $user)
    {
        // Semua user yang terautentikasi bisa download semua file
        return $query;
    }

    // ==================== Helper Methods ====================

    /**
     * Get human readable file size
     * Returns formatted size like: 1.5 KB, 2.3 MB, 1.2 GB
     */
    public function getHumanSize()
    {
        $bytes = $this->size;

        if ($bytes == 0) {
            return '0 Bytes';
        }

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2).' '.$sizes[$i];
    }

    /**
     * Accessor for human_size attribute
     */
    public function getHumanSizeAttribute()
    {
        return $this->getHumanSize();
    }

    public function getFullPath()
    {
        return $this->folder->path.'/'.$this->name;
    }

    public function incrementViews()
    {
        $this->increment('views');
        $this->update(['last_viewed_at' => now()]);
    }

    public function incrementDownloads()
    {
        $this->increment('downloads');
        $this->update(['last_downloaded_at' => now()]);
    }

    public function isLocked()
    {
        if (! $this->lock) {
            return false;
        }

        return $this->lock->expires_at->isFuture();
    }

    public function isImage()
    {
        return in_array($this->extension, self::EXTENSI_GAMBAR);
    }

    public function isPdf()
    {
        return $this->extension === 'pdf';
    }

    public function isDocument()
    {
        return in_array($this->extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
    }

    public function getDownloadUrl()
    {
        return route('terminaldata.filesData.download', $this->id);
    }

    public function getPreviewUrl()
    {
        if ($this->thumbnail_path) {
            return Storage::url($this->thumbnail_path);
        }

        if ($this->isImage()) {
            return Storage::url($this->storage_path);
        }

        return null;
    }

    public function createVersion($newFilePath, $notes = null)
    {
        // Mark current as not latest
        $this->is_latest_version = false;
        $this->save();

        // Create new version
        $newFile = new static([
            'folder_id' => $this->folder_id,
            'bidang_id' => $this->bidang_id,
            'sub_bidang_id' => $this->sub_bidang_id,
            'name' => $this->name,
            'original_name' => basename($newFilePath),
            'description' => $this->description,
            'mime_type' => mime_content_type($newFilePath),
            'extension' => pathinfo($newFilePath, PATHINFO_EXTENSION),
            'size' => filesize($newFilePath),
            'storage_path' => $newFilePath,
            'document_number' => $this->document_number,
            'document_date' => $this->document_date,
            'document_type' => $this->document_type,
            'status' => $this->status,
            'version' => $this->version + 1,
            'original_file_id' => $this->original_file_id ?? $this->id,
            'is_latest_version' => true,
            'version_notes' => $notes,
            'attachable_type' => $this->attachable_type,
            'attachable_id' => $this->attachable_id,
            'created_by' => Auth::user()->id,
        ]);

        $newFile->save();

        return $newFile;
    }

    public function duplicate($newFolderId = null, $newName = null)
    {
        $newFile = $this->replicate();
        $newFile->id = (string) Str::uuid();
        $newFile->folder_id = $newFolderId ?? $this->folder_id;
        $newFile->name = $newName ?? $this->name.' (Copy)';
        $newFile->original_file_id = null;
        $newFile->version = 1;
        $newFile->is_latest_version = true;
        $newFile->views = 0;
        $newFile->downloads = 0;
        $newFile->created_by = Auth::user()->id;
        $newFile->created_at = now();

        // If duplicating to a different folder, inherit that folder's bidang and sub_bidang
        if ($newFolderId && $newFolderId != $this->folder_id) {
            $targetFolder = TdFolder::find($newFolderId);
            if ($targetFolder) {
                $newFile->bidang_id = $targetFolder->bidang_id;
                $newFile->sub_bidang_id = $targetFolder->sub_bidang_id;
            }
        }

        // Copy physical file ke path baru yang benar-benar berbeda — sebelumnya
        // dihasilkan lewat str_replace($this->id, ...) yang tidak pernah match (UUID
        // file tidak pernah muncul di storage_path, path dibangun dari folder_id/
        // bidang_id), jadi dua baris DB berbeda berakhir menunjuk ke satu file fisik
        // yang sama — hapus salah satu ikut menghapus yang lain.
        $disk = $this->disk ?? config('filesystems.default');
        $newPathPrefix = dirname($this->storage_path);
        $newFilename = Str::uuid().'_'.basename($this->storage_path);

        $newFile->storage_path = app(\Modules\TerminalData\Services\FileManagerService::class)
            ->copyPhysical($disk, $this->storage_path, $newPathPrefix, $newFilename);
        $newFile->disk = $disk;

        $newFile->save();

        return $newFile;
    }

    // Helper methods for access control
    public function isOwnedBy($user)
    {
        return $this->created_by === $user->id;
    }

    public function belongsToUserBidang($user)
    {
        return $this->bidang_id && $this->bidang_id === $user->profile?->bidang_id;
    }

    public function belongsToUserSubBidang($user)
    {
        return $this->sub_bidang_id && $this->sub_bidang_id === $user->profile?->sub_bidang_id;
    }
}

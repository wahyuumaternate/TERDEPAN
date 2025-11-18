<?php

namespace Modules\TerminalData\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\MasterPegawai;
use App\Models\MasterBidang;
use App\Models\MasterSubBidang;
use Illuminate\Support\Facades\Auth;

// use Modules\TerminalData\Database\Factories\TdFileFactory;

class TdFile extends Model
{
    use HasFactory, SoftDeletes;

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            // Generate hash if not exists
            if (empty($model->hash) && $model->storage_path) {
                $model->hash = hash_file('sha256', Storage::path($model->storage_path));
            }
        });

        static::created(function ($model) {
            // Update folder stats
            if ($model->folder) {
                $model->folder->updateStats();
            }
        });

        static::deleting(function ($model) {
            // Delete physical file
            if ($model->storage_path && Storage::exists($model->storage_path)) {
                Storage::delete($model->storage_path);
            }

            // Delete thumbnail
            if ($model->thumbnail_path && Storage::exists($model->thumbnail_path)) {
                Storage::delete($model->thumbnail_path);
            }
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

    public function creator()
    {
        return $this->belongsTo(MasterPegawai::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(MasterPegawai::class, 'updated_by');
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
            MasterPegawai::class,
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
    public function scopeForUser($query, MasterPegawai $user)
    {
        // Semua user yang terautentikasi bisa melihat semua file
        return $query;
    }

    /**
     * Scope untuk file yang bisa diedit oleh user
     */
    public function scopeEditableBy($query, MasterPegawai $user)
    {
        $kodeJabatan = $user->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - edit semua
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return $query;
        }

        // KABID - edit file bidangnya
        if ($kodeJabatan === 'KABID') {
            return $query->where(function ($q) use ($user) {
                $q->where('bidang_id', $user->bidang_id)
                    ->orWhereHas('folder', function ($fq) use ($user) {
                        $fq->where('bidang_id', $user->bidang_id);
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
    public function scopeDeletableBy($query, MasterPegawai $user)
    {
        $kodeJabatan = $user->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - delete semua
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return $query;
        }

        // KABID - delete file bidangnya
        if ($kodeJabatan === 'KABID') {
            return $query->where(function ($q) use ($user) {
                $q->where('bidang_id', $user->bidang_id)
                    ->orWhereHas('folder', function ($fq) use ($user) {
                        $fq->where('bidang_id', $user->bidang_id);
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
    public function scopeDownloadableBy($query, MasterPegawai $user)
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

        if ($bytes == 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
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
        return $this->folder->path . '/' . $this->name;
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
        if (!$this->lock) return false;
        return $this->lock->expires_at->isFuture();
    }

    public function isImage()
    {
        return in_array($this->extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
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
        return route('td.files.download', $this->id);
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

    public function canAccess($user, $permission = 'view')
    {
        // Owner always has full access
        if ($this->created_by === $user->id) {
            return true;
        }

        // Check if public for view/download only
        if ($this->is_public && in_array($permission, ['view', 'download'])) {
            return true;
        }

        // Map permission to Spatie permission name
        $permissionMap = [
            'view' => 'td_file_view',
            'download' => 'td_file_download',
            'edit' => 'td_file_edit',
            'delete' => 'td_file_delete',
        ];

        $basePermission = $permissionMap[$permission] ?? 'td_file_view';

        // Check permissions hierarchy: all > bidang > sub_bidang > own
        if ($user->hasPermissionTo($basePermission . '_all')) {
            return true;
        }

        if (
            $user->hasPermissionTo($basePermission . '_bidang') &&
            $this->bidang_id === $user->bidang_id
        ) {
            return true;
        }

        if (
            $user->hasPermissionTo($basePermission . '_sub_bidang') &&
            $this->sub_bidang_id === $user->sub_bidang_id
        ) {
            return true;
        }

        if (
            $user->hasPermissionTo($basePermission . '_own') &&
            $this->created_by === $user->id
        ) {
            return true;
        }

        // Check folder access if file doesn't have explicit permissions
        if ($this->folder && $this->folder->canAccess($user, $permission)) {
            return true;
        }

        // Check direct shares for staff who don't have role-based permissions
        $share = $this->sharedWith()
            ->where('pegawai_id', $user->id)
            ->first();

        if ($share) {
            // Share allows view/download by default
            if (in_array($permission, ['view', 'download'])) {
                return true;
            }
            // Check if share has edit rights (if we add a permission column to pivot later)
        }

        return false;
    }

    public function duplicate($newFolderId = null, $newName = null)
    {
        $newFile = $this->replicate();
        $newFile->id = (string) Str::uuid();
        $newFile->folder_id = $newFolderId ?? $this->folder_id;
        $newFile->name = $newName ?? $this->name . ' (Copy)';
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

        // Copy physical file
        $newPath = str_replace($this->id, $newFile->id, $this->storage_path);
        Storage::copy($this->storage_path, $newPath);
        $newFile->storage_path = $newPath;

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
        return $this->bidang_id && $this->bidang_id === $user->bidang_id;
    }

    public function belongsToUserSubBidang($user)
    {
        return $this->sub_bidang_id && $this->sub_bidang_id === $user->sub_bidang_id;
    }
}

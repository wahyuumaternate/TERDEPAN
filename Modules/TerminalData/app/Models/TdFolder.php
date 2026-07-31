<?php

namespace Modules\TerminalData\Models;

use App\Models\MasterBidang;
use App\Models\MasterSubBidang;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\TerminalData\Database\Factories\TdFolderFactory;

class TdFolder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'td_folders';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'parent_id',
        'bidang_id',
        'sub_bidang_id',
        'name',
        'description',
        'path',
        'level',
        'color',
        'icon',
        'is_public',
        'is_starred',
        'is_locked',
        'is_system',
        'settings',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_public' => 'boolean',
        'is_starred' => 'boolean',
        'is_locked' => 'boolean',
        'is_system' => 'boolean',
        'total_files' => 'integer',
        'total_subfolders' => 'integer',
        'total_size' => 'integer',
    ];

    protected $with = ['creator'];

    protected static function newFactory()
    {
        return TdFolderFactory::new();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            // Calculate level
            if ($model->parent_id) {
                $parent = static::find($model->parent_id);
                $model->level = $parent ? $parent->level + 1 : 0;
            }

            $model->path = $model->generatePath();
        });

        static::updating(function ($model) {
            if ($model->isDirty(['parent_id', 'name'])) {
                $model->path = $model->generatePath();

                // Update level if parent changed
                if ($model->isDirty('parent_id')) {
                    if ($model->parent_id) {
                        $parent = static::find($model->parent_id);
                        $model->level = $parent ? $parent->level + 1 : 0;
                    } else {
                        $model->level = 0;
                    }
                }
            }
        });

        static::deleting(function ($model) {
            // Delete all subfolders and files
            $model->subfolders()->each(fn ($subfolder) => $subfolder->delete());
            $model->files()->each(fn ($file) => $file->delete());
        });

        static::deleted(function ($model) {
            // Update parent stats
            if ($model->parent) {
                $model->parent->updateStats();
            }
        });
    }

    // ==================== Relationships ====================

    public function parent()
    {
        return $this->belongsTo(TdFolder::class, 'parent_id');
    }

    public function subfolders()
    {
        return $this->hasMany(TdFolder::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(TdFile::class, 'folder_id');
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

    // ==================== Scopes ====================

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeStarred($query)
    {
        return $query->where('is_starred', true);
    }

    public function scopeNotLocked($query)
    {
        return $query->where('is_locked', false);
    }

    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeInBidang($query, $bidangId)
    {
        return $query->where('bidang_id', $bidangId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeInSubBidang($query, $subBidangId)
    {
        return $query->where('sub_bidang_id', $subBidangId);
    }

    /**
     * Scope untuk filter folder berdasarkan akses user
     * Menggunakan master_jabatan kode
     */
    public function scopeForUser($query, User $user)
    {
        // Semua user yang terautentikasi bisa melihat semua folder
        return $query;
    }

    /**
     * Scope untuk folder yang bisa diedit oleh user
     */
    public function scopeEditableBy($query, User $user)
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - edit semua
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return $query;
        }

        // KABID - edit folder bidangnya
        if ($kodeJabatan === 'KABID') {
            return $query->where('bidang_id', $user->profile?->bidang_id);
        }

        // KASUBAG, PELAKSANA, JAFUNG, GATEK - edit folder sendiri
        if (in_array($kodeJabatan, ['KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])) {
            return $query->where('created_by', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Scope untuk folder yang bisa dihapus oleh user
     */
    public function scopeDeletableBy($query, User $user)
    {
        $kodeJabatan = $user->profile?->jabatan?->kode;

        // ADMIN, KABAN, SEKBAN - delete semua
        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            return $query;
        }

        // KABID - delete folder bidangnya
        if ($kodeJabatan === 'KABID') {
            return $query->where('bidang_id', $user->profile?->bidang_id);
        }

        // KASUBAG, PELAKSANA, JAFUNG, GATEK - delete folder sendiri
        if (in_array($kodeJabatan, ['KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])) {
            return $query->where('created_by', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Scope untuk folder public atau yang dimiliki user
     */
    public function scopePublicOrOwned($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('is_public', true)
                ->orWhere('created_by', $user->id)
                ->orWhere(function ($sq) use ($user) {
                    if ($user->profile?->bidang_id) {
                        $sq->where('bidang_id', $user->profile?->bidang_id);
                    }
                });
        });
    }

    // ==================== Helper Methods ====================

    public function getBreadcrumb()
    {
        $breadcrumb = collect([$this]);
        $current = $this->parent;

        while ($current) {
            $breadcrumb->prepend($current);
            $current = $current->parent;
        }

        return $breadcrumb;
    }

    public function generatePath()
    {
        $parts = $this->getBreadcrumb()->pluck('name');

        return '/'.$parts->implode('/');
    }

    public function updateStats()
    {
        $this->total_files = $this->files()->count();
        $this->total_subfolders = $this->subfolders()->count();
        $this->total_size = $this->files()->sum('size');
        $this->save();

        // Recursively update parent stats
        if ($this->parent) {
            $this->parent->updateStats();
        }
    }

    public function getHumanSize()
    {
        $bytes = $this->total_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Check if user can access this folder
     * Menggunakan Spatie Permission untuk checking
     */
    public function canAccess(User $user, $permission = 'view')
    {
        // Owner always has access
        if ($this->created_by === $user->id) {
            return true;
        }

        // Check if public and only viewing
        if ($this->is_public && $permission === 'view') {
            return true;
        }

        // Check permission based access
        if ($permission === 'view') {
            // View all permission
            if ($user->hasPermissionTo('td_folder_view_all')) {
                return true;
            }

            // View bidang permission
            if ($user->hasPermissionTo('td_folder_view_bidang') && $this->bidang_id === $user->profile?->bidang_id) {
                return true;
            }

            // View sub bidang permission
            if ($user->hasPermissionTo('td_folder_view_sub_bidang') && $this->sub_bidang_id === $user->profile?->sub_bidang_id) {
                return true;
            }
        }

        if ($permission === 'edit') {
            // Edit bidang permission
            if ($user->hasPermissionTo('td_folder_edit_bidang') && $this->bidang_id === $user->profile?->bidang_id) {
                return true;
            }

            // Edit own permission
            if ($user->hasPermissionTo('td_folder_edit_own') && $this->created_by === $user->id) {
                return true;
            }
        }

        if ($permission === 'delete') {
            // Delete bidang permission
            if ($user->hasPermissionTo('td_folder_delete_bidang') && $this->bidang_id === $user->profile?->bidang_id) {
                return true;
            }

            // Delete own permission
            if ($user->hasPermissionTo('td_folder_delete_own') && $this->created_by === $user->id) {
                return true;
            }
        }

        // Check shares jika ada
        if (method_exists($this, 'shares') && $this->shares()->exists()) {
            $share = $this->shares()
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhere('bidang_id', $user->profile?->bidang_id);
                })
                ->where(function ($q) use ($permission) {
                    $levels = ['viewer', 'commenter', 'editor', 'owner'];
                    $minIndex = array_search($permission === 'view' ? 'viewer' : $permission, $levels);
                    if ($minIndex !== false) {
                        $allowedLevels = array_slice($levels, $minIndex);
                        $q->whereIn('access_level', $allowedLevels);
                    }
                })
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->first();

            if ($share) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is owner of this folder
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->created_by === $user->id;
    }

    /**
     * Check if folder belongs to user's bidang
     */
    public function belongsToUserBidang(User $user): bool
    {
        return $this->bidang_id === $user->profile?->bidang_id;
    }

    /**
     * Check if folder belongs to user's sub bidang
     */
    public function belongsToUserSubBidang(User $user): bool
    {
        return $this->sub_bidang_id === $user->profile?->sub_bidang_id;
    }

    public function getAllDescendants()
    {
        $descendants = collect();

        foreach ($this->subfolders as $subfolder) {
            $descendants->push($subfolder);
            $descendants = $descendants->merge($subfolder->getAllDescendants());
        }

        return $descendants;
    }

    public function move($newParentId)
    {
        // Prevent moving to itself or its descendants
        if ($newParentId === $this->id) {
            throw new \Exception('Cannot move folder to itself');
        }

        $descendants = $this->getAllDescendants()->pluck('id');
        if ($descendants->contains($newParentId)) {
            throw new \Exception('Cannot move folder to its descendant');
        }

        $this->parent_id = $newParentId;
        $this->save();

        // Update paths for all descendants
        $this->updateDescendantPaths();
    }

    protected function updateDescendantPaths()
    {
        foreach ($this->subfolders as $subfolder) {
            $subfolder->path = $subfolder->generatePath();
            $subfolder->level = $this->level + 1;
            $subfolder->saveQuietly(); // Save without triggering events
            $subfolder->updateDescendantPaths();
        }
    }
}

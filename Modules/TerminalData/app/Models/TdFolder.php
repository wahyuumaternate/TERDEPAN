<?php

namespace Modules\TerminalData\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\MasterPegawai;
use App\Models\MasterBidang;
// use Modules\TerminalData\Database\Factories\TdFolderFactory;

class TdFolder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'td_folders';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'parent_id',
        'bidang_id',
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
            $model->subfolders()->each(fn($subfolder) => $subfolder->delete());
            $model->files()->each(fn($file) => $file->delete());
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
        return '/' . $parts->implode('/');
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

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function canAccess($user, $permission = 'viewer')
    {
        // Owner always has access
        if ($this->created_by === $user->id) {
            return true;
        }

        // Check if public
        if ($this->is_public && $permission === 'viewer') {
            return true;
        }

        // Check shares
        $share = $this->shares()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('bidang_id', $user->bidang_id);
            })
            ->where(function ($q) use ($permission) {
                $levels = ['viewer', 'commenter', 'editor', 'owner'];
                $minIndex = array_search($permission, $levels);
                $allowedLevels = array_slice($levels, $minIndex);
                $q->whereIn('access_level', $allowedLevels);
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        return $share !== null;
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

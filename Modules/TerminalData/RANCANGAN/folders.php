<?php

// ============================================================================
// 1. MIGRATION: create_td_folders_table.php
// Path: Modules/TerminalData/Database/Migrations/xxxx_xx_xx_create_td_folders_table.php
// ============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('td_folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_id')->nullable()->index();
            $table->foreignId('bidang_id')->nullable()->constrained('master_bidang')->nullOnDelete();
            
            // Folder Info
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('path')->index();
            $table->integer('level')->default(0);
            $table->string('color', 7)->nullable()->comment('Hex color untuk UI');
            $table->string('icon')->nullable()->comment('Icon name/class');
            
            // Permissions & Settings
            $table->boolean('is_public')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_locked')->default(false)->comment('Prevent deletion/modification');
            $table->boolean('is_system')->default(false)->comment('System generated folder');
            
            // Stats
            $table->unsignedInteger('total_files')->default(0);
            $table->unsignedInteger('total_subfolders')->default(0);
            $table->unsignedBigInteger('total_size')->default(0)->comment('Total size in bytes');
            
            // Metadata
            $table->json('settings')->nullable()->comment('Custom folder settings');
            
            // Ownership
            $table->foreignId('created_by')->constrained('master_pegawai')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('master_pegawai')->restrictOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['parent_id', 'level']);
            $table->index(['created_by', 'is_starred']);
            $table->fullText(['name', 'description']);
            
            // Foreign key
            $table->foreign('parent_id')->references('id')->on('td_folders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('td_folders');
    }
};

// ============================================================================
// 2. MODEL: TdFolder.php
// Path: Modules/TerminalData/Entities/TdFolder.php
// ============================================================================

namespace Modules\TerminalData\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Models\MasterPegawai;
use App\Models\MasterBidang;

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
        return $query->where(function($q) use ($search) {
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
            ->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('bidang_id', $user->bidang_id);
            })
            ->where(function($q) use ($permission) {
                $levels = ['viewer', 'commenter', 'editor', 'owner'];
                $minIndex = array_search($permission, $levels);
                $allowedLevels = array_slice($levels, $minIndex);
                $q->whereIn('access_level', $allowedLevels);
            })
            ->where(function($q) {
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

// ============================================================================
// 3. CONTROLLER: TdFolderController.php
// Path: Modules/TerminalData/Http/Controllers/Api/TdFolderController.php
// ============================================================================

namespace Modules\TerminalData\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TerminalData\Entities\TdFolder;
use Modules\TerminalData\Http\Requests\StoreTdFolderRequest;
use Modules\TerminalData\Http\Requests\UpdateTdFolderRequest;
use Modules\TerminalData\Http\Resources\TdFolderResource;
use Modules\TerminalData\Services\TdFolderService;

class TdFolderController extends Controller
{
    protected $folderService;
    
    public function __construct(TdFolderService $folderService)
    {
        $this->folderService = $folderService;
        
        $this->middleware('auth:sanctum');
        $this->middleware('can:view,folder')->only(['show']);
        $this->middleware('can:update,folder')->only(['update']);
        $this->middleware('can:delete,folder')->only(['destroy']);
    }
    
    /**
     * Display a listing of folders
     */
    public function index(Request $request): JsonResponse
    {
        $folders = TdFolder::query()
            ->with(['creator', 'bidang', 'tags'])
            ->when($request->parent_id, fn($q) => $q->where('parent_id', $request->parent_id))
            ->when($request->bidang_id, fn($q) => $q->where('bidang_id', $request->bidang_id))
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->starred, fn($q) => $q->starred())
            ->when($request->is_root, fn($q) => $q->roots())
            ->orderBy($request->sort_by ?? 'name', $request->sort_order ?? 'asc')
            ->paginate($request->per_page ?? 15);
        
        return response()->json([
            'success' => true,
            'data' => TdFolderResource::collection($folders),
            'meta' => [
                'total' => $folders->total(),
                'per_page' => $folders->perPage(),
                'current_page' => $folders->currentPage(),
            ]
        ]);
    }
    
    /**
     * Store a newly created folder
     */
    public function store(StoreTdFolderRequest $request): JsonResponse
    {
        try {
            $folder = $this->folderService->create($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil dibuat',
                'data' => new TdFolderResource($folder)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat folder: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the specified folder
     */
    public function show(TdFolder $folder): JsonResponse
    {
        $folder->load(['creator', 'bidang', 'subfolders', 'files', 'tags']);
        
        return response()->json([
            'success' => true,
            'data' => new TdFolderResource($folder)
        ]);
    }
    
    /**
     * Update the specified folder
     */
    public function update(UpdateTdFolderRequest $request, TdFolder $folder): JsonResponse
    {
        try {
            $folder = $this->folderService->update($folder, $request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil diupdate',
                'data' => new TdFolderResource($folder)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update folder: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove the specified folder
     */
    public function destroy(TdFolder $folder): JsonResponse
    {
        try {
            $this->folderService->delete($folder);
            
            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus folder: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get folder breadcrumb
     */
    public function breadcrumb(TdFolder $folder): JsonResponse
    {
        $breadcrumb = $folder->getBreadcrumb();
        
        return response()->json([
            'success' => true,
            'data' => TdFolderResource::collection($breadcrumb)
        ]);
    }
    
    /**
     * Move folder to another parent
     */
    public function move(Request $request, TdFolder $folder): JsonResponse
    {
        $request->validate([
            'parent_id' => 'nullable|uuid|exists:td_folders,id'
        ]);
        
        try {
            $this->folderService->move($folder, $request->parent_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil dipindahkan',
                'data' => new TdFolderResource($folder->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Toggle star status
     */
    public function toggleStar(TdFolder $folder): JsonResponse
    {
        $folder->update(['is_starred' => !$folder->is_starred]);
        
        return response()->json([
            'success' => true,
            'message' => $folder->is_starred ? 'Folder ditandai' : 'Tanda dihapus',
            'data' => new TdFolderResource($folder)
        ]);
    }
    
    /**
     * Get folder statistics
     */
    public function stats(TdFolder $folder): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_files' => $folder->total_files,
                'total_subfolders' => $folder->total_subfolders,
                'total_size' => $folder->total_size,
                'human_size' => $folder->getHumanSize(),
                'level' => $folder->level,
            ]
        ]);
    }
}

// ============================================================================
// 4. REQUEST: StoreTdFolderRequest.php
// Path: Modules/TerminalData/Http/Requests/StoreTdFolderRequest.php
// ============================================================================

namespace Modules\TerminalData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTdFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'parent_id' => 'nullable|uuid|exists:td_folders,id',
            'bidang_id' => 'nullable|exists:master_bidang,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:100',
            'is_public' => 'boolean',
            'settings' => 'nullable|array',
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => 'Nama folder wajib diisi',
            'name.max' => 'Nama folder maksimal 255 karakter',
            'parent_id.exists' => 'Parent folder tidak ditemukan',
            'bidang_id.exists' => 'Bidang tidak ditemukan',
            'color.regex' => 'Format warna harus hexadecimal (contoh: #FF5733)',
        ];
    }
}

// ============================================================================
// 5. REQUEST: UpdateTdFolderRequest.php
// Path: Modules/TerminalData/Http/Requests/UpdateTdFolderRequest.php
// ============================================================================

namespace Modules\TerminalData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTdFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:100',
            'is_public' => 'boolean',
            'is_starred' => 'boolean',
            'settings' => 'nullable|array',
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.max' => 'Nama folder maksimal 255 karakter',
            'color.regex' => 'Format warna harus hexadecimal (contoh: #FF5733)',
        ];
    }
}

// ============================================================================
// 6. RESOURCE: TdFolderResource.php
// Path: Modules/TerminalData/Http/Resources/TdFolderResource.php
// ============================================================================

namespace Modules\TerminalData\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TdFolderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'bidang_id' => $this->bidang_id,
            
            // Basic Info
            'name' => $this->name,
            'description' => $this->description,
            'path' => $this->path,
            'level' => $this->level,
            
            // UI
            'color' => $this->color,
            'icon' => $this->icon ?? 'folder',
            
            // Flags
            'is_public' => $this->is_public,
            'is_starred' => $this->is_starred,
            'is_locked' => $this->is_locked,
            'is_system' => $this->is_system,
            
            // Stats
            'total_files' => $this->total_files,
            'total_subfolders' => $this->total_subfolders,
            'total_size' => $this->total_size,
            'human_size' => $this->getHumanSize(),
            
            // Settings
            'settings' => $this->settings,
            
            // Relationships
            'creator' => [
                'id' => $this->creator->id,
                'nama' => $this->creator->nama,
                'nip' => $this->creator->nip,
            ],
            'bidang' => $this->when($this->bidang, [
                'id' => $this->bidang?->id,
                'nama' => $this->bidang?->nama,
            ]),
            'parent' => $this->when($this->parent, fn() => [
                'id' => $this->parent->id,
                'name' => $this->parent->name,
            ]),
            
            // Load when included
            'subfolders' => TdFolderResource::collection($this->whenLoaded('subfolders')),
            'files' => TdFileResource::collection($this->whenLoaded('files')),
            'tags' => TdTagResource::collection($this->whenLoaded('tags')),
            
            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            
            // Computed
            'can_edit' => $this->when(auth()->check(), fn() => 
                $this->created_by === auth()->id() || auth()->user()->hasRole('admin')
            ),
            'can_delete' => $this->when(auth()->check(), fn() => 
                !$this->is_system && ($this->created_by === auth()->id() || auth()->user()->hasRole('admin'))
            ),
        ];
    }
}

// ============================================================================
// 7. SERVICE: TdFolderService.php
// Path: Modules/TerminalData/Services/TdFolderService.php
// ============================================================================

namespace Modules\TerminalData\Services;

use Modules\TerminalData\Entities\TdFolder;
use Modules\TerminalData\Repositories\TdFolderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TdFolderService
{
    protected $folderRepository;
    protected $activityService;
    
    public function __construct(
        TdFolderRepository $folderRepository,
        TdActivityService $activityService
    ) {
        $this->folderRepository = $folderRepository;
        $this->activityService = $activityService;
    }
    
    /**
     * Create new folder
     */
    public function create(array $data): TdFolder
    {
        DB::beginTransaction();
        
        try {
            // Set creator
            $data['created_by'] = auth()->id();
            
            // Create folder
            $folder = $this->folderRepository->create($data);
            
            // Update parent stats if exists
            if ($folder->parent) {
                $folder->parent->updateStats();
            }
            
            // Log activity
            $this->activityService->log($folder, 'create', 'Folder dibuat');
            
            DB::commit();
            
            return $folder->fresh(['creator', 'bidang']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating folder: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update folder
     */
    public function update(TdFolder $folder, array $data): TdFolder
    {
        DB::beginTransaction();
        
        try {
            // Check if locked
            if ($folder->is_locked && !auth()->user()->hasRole('admin')) {
                throw new \Exception('Folder terkunci dan tidak bisa diubah');
            }
            
            // Set updater
            $data['updated_by'] = auth()->id();
            
            // Store old values for activity log
            $oldValues = $folder->only(['name', 'description', 'parent_id']);
            
            // Update folder
            $folder = $this->folderRepository->update($folder, $data);
            
            // Log activity
            $this->activityService->log($folder, 'edit', 'Folder diupdate', [
                'old' => $oldValues,
                'new' => $folder->only(['name', 'description', 'parent_id'])
            ]);
            
            DB::commit();
            
            return $folder->fresh(['creator', 'bidang']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating folder: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Delete folder
     */
    public function delete(TdFolder $folder): bool
    {
        DB::beginTransaction();
        
        try {
            // Check if system folder
            if ($folder->is_system) {
                throw new \Exception('System folder tidak bisa dihapus');
            }
            
            // Check if locked
            if ($folder->is_locked && !auth()->user()->hasRole('admin')) {
                throw new \Exception('Folder terkunci dan tidak bisa dihapus');
            }
            
            // Log activity before delete
            $this->activityService->log($folder, 'delete', 'Folder dihapus');
            
            // Delete folder (will cascade to subfolders and files)
            $result = $this->folderRepository->delete($folder);
            
            DB::commit();
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting folder: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Move folder to new parent
     */
    public function move(TdFolder $folder, ?string $newParentId): TdFolder
    {
        DB::beginTransaction();
        
        try {
            $oldParent = $folder->parent;
            
            // Move folder
            $folder->move($newParentId);
            
            // Update old parent stats
            if ($oldParent) {
                $oldParent->updateStats();
            }
            
            // Update new parent stats
            if ($folder->parent) {
                $folder->parent->updateStats();
            }
            
            // Log activity
            $this->activityService->log($folder, 'move', 'Folder dipindahkan', [
                'from' => $oldParent?->name ?? 'Root',
                'to' => $folder->parent?->name ?? 'Root'
            ]);
            
            DB::commit();
            
            return $folder->fresh(['creator', 'bidang', 'parent']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error moving folder: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Copy folder to new parent
     */
    public function copy(TdFolder $folder, ?string $newParentId, string $newName = null): TdFolder
    {
        DB::beginTransaction();
        
        try {
            $data = $folder->toArray();
            $data['parent_id'] = $newParentId;
            $data['name'] = $newName ?? $folder->name . ' (Copy)';
            $data['created_by'] = auth()->id();
            
            // Remove stats and dates
            unset($data['id'], $data['total_files'], $data['total_subfolders'], 
                  $data['total_size'], $data['created_at'], $data['updated_at']);
            
            $newFolder = $this->create($data);
            
            // Copy subfolders recursively
            foreach ($folder->subfolders as $subfolder) {
                $this->copy($subfolder, $newFolder->id);
            }
            
            // Note: Files should be copied separately with actual file duplication
            
            DB::commit();
            
            return $newFolder;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error copying folder: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get folder tree structure
     */
    public function getTree(?string $parentId = null, int $depth = 3): array
    {
        return $this->folderRepository->getTree($parentId, $depth);
    }
    
    /**
     * Search folders
     */
    public function search(string $query, array $filters = [])
    {
        return $this->folderRepository->search($query, $filters);
    }
    
    /**
     * Get shared folders for user
     */
    public function getSharedFolders($userId)
    {
        return $this->folderRepository->getSharedFolders($userId);
    }
    
    /**
     * Create system folder (e.g., for each bidang)
     */
    public function createSystemFolder(array $data): TdFolder
    {
        $data['is_system'] = true;
        $data['is_locked'] = true;
        $data['created_by'] = $data['created_by'] ?? 1; // System user
        
        return $this->create($data);
    }
}

// ============================================================================
// 8. REPOSITORY: TdFolderRepository.php
// Path: Modules/TerminalData/Repositories/TdFolderRepository.php
// ============================================================================

namespace Modules\TerminalData\Repositories;

use Modules\TerminalData\Entities\TdFolder;
use Illuminate\Database\Eloquent\Collection;

class TdFolderRepository
{
    /**
     * Create folder
     */
    public function create(array $data): TdFolder
    {
        return TdFolder::create($data);
    }
    
    /**
     * Update folder
     */
    public function update(TdFolder $folder, array $data): TdFolder
    {
        $folder->update($data);
        return $folder;
    }
    
    /**
     * Delete folder
     */
    public function delete(TdFolder $folder): bool
    {
        return $folder->delete();
    }
    
    /**
     * Find folder by ID
     */
    public function find(string $id): ?TdFolder
    {
        return TdFolder::find($id);
    }
    
    /**
     * Get folder tree structure
     */
    public function getTree(?string $parentId = null, int $depth = 3, int $currentDepth = 0): array
    {
        if ($currentDepth >= $depth) {
            return [];
        }
        
        $folders = TdFolder::where('parent_id', $parentId)
            ->with(['creator', 'bidang'])
            ->orderBy('name')
            ->get();
        
        return $folders->map(function ($folder) use ($depth, $currentDepth) {
            return [
                'id' => $folder->id,
                'name' => $folder->name,
                'path' => $folder->path,
                'level' => $folder->level,
                'color' => $folder->color,
                'icon' => $folder->icon,
                'total_files' => $folder->total_files,
                'total_subfolders' => $folder->total_subfolders,
                'is_public' => $folder->is_public,
                'is_locked' => $folder->is_locked,
                'children' => $this->getTree($folder->id, $depth, $currentDepth + 1)
            ];
        })->toArray();
    }
    
    /**
     * Search folders
     */
    public function search(string $query, array $filters = [])
    {
        $queryBuilder = TdFolder::query()
            ->with(['creator', 'bidang', 'tags'])
            ->search($query);
        
        if (isset($filters['bidang_id'])) {
            $queryBuilder->where('bidang_id', $filters['bidang_id']);
        }
        
        if (isset($filters['created_by'])) {
            $queryBuilder->where('created_by', $filters['created_by']);
        }
        
        if (isset($filters['is_public'])) {
            $queryBuilder->where('is_public', $filters['is_public']);
        }
        
        return $queryBuilder->get();
    }
    
    /**
     * Get shared folders for user
     */
    public function getSharedFolders($userId): Collection
    {
        return TdFolder::whereHas('shares', function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->where(function($q) {
                  $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
              });
        })->with(['creator', 'bidang'])->get();
    }
    
    /**
     * Get root folders
     */
    public function getRootFolders(): Collection
    {
        return TdFolder::roots()
            ->with(['creator', 'bidang'])
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Get starred folders for user
     */
    public function getStarredFolders($userId): Collection
    {
        return TdFolder::where('created_by', $userId)
            ->starred()
            ->with(['creator', 'bidang'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }
}

// ============================================================================
// 9. POLICY: TdFolderPolicy.php
// Path: Modules/TerminalData/Policies/TdFolderPolicy.php
// ============================================================================

namespace Modules\TerminalData\Policies;

use App\Models\User;
use Modules\TerminalData\Entities\TdFolder;
use Illuminate\Auth\Access\HandlesAuthorization;

class TdFolderPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine if user can view any folders
     */
    public function viewAny(User $user): bool
    {
        return true;
    }
    
    /**
     * Determine if user can view the folder
     */
    public function view(User $user, TdFolder $folder): bool
    {
        // Owner can always view
        if ($folder->created_by === $user->id) {
            return true;
        }
        
        // Public folders
        if ($folder->is_public) {
            return true;
        }
        
        // Check shares
        return $folder->canAccess($user, 'viewer');
    }
    
    /**
     * Determine if user can create folders
     */
    public function create(User $user): bool
    {
        return true;
    }
    
    /**
     * Determine if user can update the folder
     */
    public function update(User $user, TdFolder $folder): bool
    {
        // System folders can only be edited by admin
        if ($folder->is_system && !$user->hasRole('admin')) {
            return false;
        }
        
        // Locked folders can only be edited by admin
        if ($folder->is_locked && !$user->hasRole('admin')) {
            return false;
        }
        
        // Owner can update
        if ($folder->created_by === $user->id) {
            return true;
        }
        
        // Check if user has editor access through shares
        return $folder->canAccess($user, 'editor');
    }
    
    /**
     * Determine if user can delete the folder
     */
    public function delete(User $user, TdFolder $folder): bool
    {
        // System folders cannot be deleted
        if ($folder->is_system) {
            return false;
        }
        
        // Locked folders can only be deleted by admin
        if ($folder->is_locked && !$user->hasRole('admin')) {
            return false;
        }
        
        // Owner can delete
        if ($folder->created_by === $user->id) {
            return true;
        }
        
        // Admins can delete
        return $user->hasRole('admin');
    }
    
    /**
     * Determine if user can share the folder
     */
    public function share(User $user, TdFolder $folder): bool
    {
        // Owner can share
        if ($folder->created_by === $user->id) {
            return true;
        }
        
        // Check if user has share permission through existing shares
        $share = $folder->shares()
            ->where('user_id', $user->id)
            ->first();
            
        return $share && $share->can_share;
    }
}

// ============================================================================
// 10. SEEDER: TdFolderSeeder.php
// Path: Modules/TerminalData/Database/Seeders/TdFolderSeeder.php
// ============================================================================

namespace Modules\TerminalData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\TerminalData\Entities\TdFolder;
use App\Models\MasterBidang;
use App\Models\MasterPegawai;

class TdFolderSeeder extends Seeder
{
    public function run(): void
    {
        $pegawai = MasterPegawai::first();
        $bidangs = MasterBidang::all();
        
        if (!$pegawai) {
            $this->command->warn('No pegawai found. Skipping folder seeder.');
            return;
        }
        
        // Create root folders for each bidang
        foreach ($bidangs as $bidang) {
            $rootFolder = TdFolder::create([
                'name' => $bidang->nama,
                'description' => 'Folder utama untuk ' . $bidang->nama,
                'bidang_id' => $bidang->id,
                'is_system' => true,
                'is_locked' => true,
                'color' => $this->getRandomColor(),
                'icon' => 'building-office',
                'created_by' => $pegawai->id,
            ]);
            
            // Create subfolders
            $subfolders = [
                ['name' => 'Surat Masuk', 'icon' => 'inbox-arrow-down'],
                ['name' => 'Surat Keluar', 'icon' => 'paper-airplane'],
                ['name' => 'SK & Keputusan', 'icon' => 'document-check'],
                ['name' => 'Laporan', 'icon' => 'document-chart-bar'],
                ['name' => 'Dokumen Umum', 'icon' => 'folder'],
            ];
            
            foreach ($subfolders as $subfolder) {
                TdFolder::create([
                    'parent_id' => $rootFolder->id,
                    'name' => $subfolder['name'],
                    'bidang_id' => $bidang->id,
                    'icon' => $subfolder['icon'],
                    'color' => $this->getRandomColor(),
                    'created_by' => $pegawai->id,
                ]);
            }
        }
        
        // Create personal folders
        TdFolder::create([
            'name' => 'My Documents',
            'description' => 'Personal documents folder',
            'icon' => 'user-circle',
            'color' => '#3B82F6',
            'created_by' => $pegawai->id,
        ]);
        
        $this->command->info('Folder seeder completed successfully!');
    }
    
    private function getRandomColor(): string
    {
        $colors = [
            '#EF4444', '#F59E0B', '#10B981', '#3B82F6', 
            '#6366F1', '#8B5CF6', '#EC4899', '#14B8A6'
        ];
        
        return $colors[array_rand($colors)];
    }
}

// ============================================================================
// 11. ROUTES: api.php
// Path: Modules/TerminalData/Routes/api.php
// ============================================================================

use Illuminate\Support\Facades\Route;
use Modules\TerminalData\Http\Controllers\Api\TdFolderController;

/*
|--------------------------------------------------------------------------
| API Routes - Terminal Data Folders
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->prefix('terminal-data')->group(function () {
    
    // Folder Routes
    Route::prefix('folders')->name('td.folders.')->group(function () {
        // CRUD Operations
        Route::get('/', [TdFolderController::class, 'index'])->name('index');
        Route::post('/', [TdFolderController::class, 'store'])->name('store');
        Route::get('/{folder}', [TdFolderController::class, 'show'])->name('show');
        Route::put('/{folder}', [TdFolderController::class, 'update'])->name('update');
        Route::delete('/{folder}', [TdFolderController::class, 'destroy'])->name('destroy');
        
        // Additional Actions
        Route::get('/{folder}/breadcrumb', [TdFolderController::class, 'breadcrumb'])->name('breadcrumb');
        Route::post('/{folder}/move', [TdFolderController::class, 'move'])->name('move');
        Route::post('/{folder}/toggle-star', [TdFolderController::class, 'toggleStar'])->name('toggle-star');
        Route::get('/{folder}/stats', [TdFolderController::class, 'stats'])->name('stats');
    });
});

// ============================================================================
// 12. FEATURE TEST: TdFolderTest.php
// Path: Modules/TerminalData/Tests/Feature/TdFolderTest.php
// ============================================================================

namespace Modules\TerminalData\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\TerminalData\Entities\TdFolder;
use App\Models\MasterPegawai;
use App\Models\MasterBidang;
use App\Models\User;

class TdFolderTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    protected $user;
    protected $pegawai;
    protected $bidang;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user and pegawai
        $this->user = User::factory()->create();
        $this->pegawai = MasterPegawai::factory()->create([
            'user_id' => $this->user->id
        ]);
        $this->bidang = MasterBidang::factory()->create();
    }
    
    /** @test */
    public function it_can_list_folders()
    {
        TdFolder::factory()->count(5)->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $response = $this->actingAs($this->user)
            ->getJson('/api/terminal-data/folders');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'path',
                        'created_at'
                    ]
                ],
                'meta'
            ]);
    }
    
    /** @test */
    public function it_can_create_a_folder()
    {
        $data = [
            'name' => 'Test Folder',
            'description' => 'This is a test folder',
            'bidang_id' => $this->bidang->id,
            'color' => '#FF5733',
            'icon' => 'folder',
        ];
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/terminal-data/folders', $data);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'path'
                ]
            ]);
        
        $this->assertDatabaseHas('td_folders', [
            'name' => 'Test Folder',
            'created_by' => $this->pegawai->id
        ]);
    }
    
    /** @test */
    public function it_can_create_nested_folder()
    {
        $parent = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $data = [
            'name' => 'Subfolder',
            'parent_id' => $parent->id,
        ];
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/terminal-data/folders', $data);
        
        $response->assertStatus(201);
        
        $folder = TdFolder::where('name', 'Subfolder')->first();
        $this->assertEquals($parent->id, $folder->parent_id);
        $this->assertEquals($parent->level + 1, $folder->level);
    }
    
    /** @test */
    public function it_can_show_a_folder()
    {
        $folder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $response = $this->actingAs($this->user)
            ->getJson("/api/terminal-data/folders/{$folder->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $folder->id,
                    'name' => $folder->name
                ]
            ]);
    }
    
    /** @test */
    public function it_can_update_a_folder()
    {
        $folder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $data = [
            'name' => 'Updated Folder Name',
            'description' => 'Updated description'
        ];
        
        $response = $this->actingAs($this->user)
            ->putJson("/api/terminal-data/folders/{$folder->id}", $data);
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('td_folders', [
            'id' => $folder->id,
            'name' => 'Updated Folder Name'
        ]);
    }
    
    /** @test */
    public function it_can_delete_a_folder()
    {
        $folder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/terminal-data/folders/{$folder->id}");
        
        $response->assertStatus(200);
        
        $this->assertSoftDeleted('td_folders', [
            'id' => $folder->id
        ]);
    }
    
    /** @test */
    public function it_cannot_delete_system_folder()
    {
        $folder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id,
            'is_system' => true
        ]);
        
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/terminal-data/folders/{$folder->id}");
        
        $response->assertStatus(500);
        
        $this->assertDatabaseHas('td_folders', [
            'id' => $folder->id,
            'deleted_at' => null
        ]);
    }
    
    /** @test */
    public function it_can_move_folder()
    {
        $folder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $newParent = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $response = $this->actingAs($this->user)
            ->postJson("/api/terminal-data/folders/{$folder->id}/move", [
                'parent_id' => $newParent->id
            ]);
        
        $response->assertStatus(200);
        
        $folder->refresh();
        $this->assertEquals($newParent->id, $folder->parent_id);
    }
    
    /** @test */
    public function it_cannot_move_folder_to_itself()
    {
        $folder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $response = $this->actingAs($this->user)
            ->postJson("/api/terminal-data/folders/{$folder->id}/move", [
                'parent_id' => $folder->id
            ]);
        
        $response->assertStatus(400);
    }
    
    /** @test */
    public function it_can_toggle_star()
    {
        $folder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id,
            'is_starred' => false
        ]);
        
        $response = $this->actingAs($this->user)
            ->postJson("/api/terminal-data/folders/{$folder->id}/toggle-star");
        
        $response->assertStatus(200);
        
        $folder->refresh();
        $this->assertTrue($folder->is_starred);
    }
    
    /** @test */
    public function it_can_get_breadcrumb()
    {
        $root = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $child = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id,
            'parent_id' => $root->id
        ]);
        
        $grandchild = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id,
            'parent_id' => $child->id
        ]);
        
        $response = $this->actingAs($this->user)
            ->getJson("/api/terminal-data/folders/{$grandchild->id}/breadcrumb");
        
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
    
    /** @test */
    public function it_can_get_folder_stats()
    {
        $folder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $response = $this->actingAs($this->user)
            ->getJson("/api/terminal-data/folders/{$folder->id}/stats");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_files',
                    'total_subfolders',
                    'total_size',
                    'human_size',
                    'level'
                ]
            ]);
    }
    
    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/terminal-data/folders', []);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
    
    /** @test */
    public function it_validates_color_format()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/terminal-data/folders', [
                'name' => 'Test',
                'color' => 'invalid-color'
            ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['color']);
    }
}

// ============================================================================
// 13. UNIT TEST: TdFolderServiceTest.php
// Path: Modules/TerminalData/Tests/Unit/TdFolderServiceTest.php
// ============================================================================

namespace Modules\TerminalData\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\TerminalData\Entities\TdFolder;
use Modules\TerminalData\Services\TdFolderService;
use Modules\TerminalData\Repositories\TdFolderRepository;
use App\Models\MasterPegawai;
use App\Models\User;
use Mockery;

class TdFolderServiceTest extends TestCase
{
    use RefreshDatabase;
    
    protected $folderService;
    protected $user;
    protected $pegawai;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->pegawai = MasterPegawai::factory()->create([
            'user_id' => $this->user->id
        ]);
        $this->actingAs($this->user);
        
        $this->folderService = app(TdFolderService::class);
    }
    
    /** @test */
    public function it_can_create_folder()
    {
        $data = [
            'name' => 'Test Folder',
            'description' => 'Test Description',
        ];
        
        $folder = $this->folderService->create($data);
        
        $this->assertInstanceOf(TdFolder::class, $folder);
        $this->assertEquals('Test Folder', $folder->name);
        $this->assertEquals($this->pegawai->id, $folder->created_by);
    }
    
    /** @test */
    public function it_updates_parent_stats_after_create()
    {
        $parent = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $data = [
            'name' => 'Child Folder',
            'parent_id' => $parent->id,
        ];
        
        $this->folderService->create($data);
        
        $parent->refresh();
        $this->assertEquals(1, $parent->total_subfolders);
    }
    
    /** @test */
    public function it_can_update_folder()
    {
        $folder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $data = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ];
        
        $updated = $this->folderService->update($folder, $data);
        
        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals('Updated Description', $updated->description);
    }
    
    /** @test */
    public function it_cannot_update_locked_folder()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Folder terkunci');
        
        $folder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id,
            'is_locked' => true
        ]);
        
        $this->folderService->update($folder, ['name' => 'New Name']);
    }
    
    /** @test */
    public function it_can_delete_folder()
    {
        $folder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $result = $this->folderService->delete($folder);
        
        $this->assertTrue($result);
        $this->assertSoftDeleted('td_folders', ['id' => $folder->id]);
    }
    
    /** @test */
    public function it_cannot_delete_system_folder()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('System folder tidak bisa dihapus');
        
        $folder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id,
            'is_system' => true
        ]);
        
        $this->folderService->delete($folder);
    }
    
    /** @test */
    public function it_can_move_folder()
    {
        $folder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $newParent = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $moved = $this->folderService->move($folder, $newParent->id);
        
        $this->assertEquals($newParent->id, $moved->parent_id);
    }
    
    /** @test */
    public function it_cannot_move_folder_to_descendant()
    {
        $this->expectException(\Exception::class);
        
        $parent = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $child = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id,
            'parent_id' => $parent->id
        ]);
        
        $this->folderService->move($parent, $child->id);
    }
    
    /** @test */
    public function it_can_copy_folder()
    {
        $folder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id,
            'name' => 'Original'
        ]);
        
        $copied = $this->folderService->copy($folder, null, 'Copied');
        
        $this->assertEquals('Copied', $copied->name);
        $this->assertNotEquals($folder->id, $copied->id);
    }
    
    /** @test */
    public function it_can_create_system_folder()
    {
        $data = [
            'name' => 'System Folder',
        ];
        
        $folder = $this->folderService->createSystemFolder($data);
        
        $this->assertTrue($folder->is_system);
        $this->assertTrue($folder->is_locked);
    }
    
    /** @test */
    public function it_calculates_path_correctly()
    {
        $root = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id,
            'name' => 'Root'
        ]);
        
        $child = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id,
            'parent_id' => $root->id,
            'name' => 'Child'
        ]);
        
        $this->assertEquals('/Root/Child', $child->path);
    }
}

// ============================================================================
// 14. FACTORY: TdFolderFactory.php
// Path: Modules/TerminalData/Database/Factories/TdFolderFactory.php
// ============================================================================

namespace Modules\TerminalData\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\TerminalData\Entities\TdFolder;
use App\Models\MasterPegawai;
use App\Models\MasterBidang;

class TdFolderFactory extends Factory
{
    protected $model = TdFolder::class;
    
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'color' => $this->faker->hexColor(),
            'icon' => $this->faker->randomElement(['folder', 'inbox', 'document', 'archive']),
            'is_public' => $this->faker->boolean(30),
            'is_starred' => $this->faker->boolean(20),
            'is_locked' => false,
            'is_system' => false,
            'created_by' => MasterPegawai::factory(),
        ];
    }
    
    public function withParent($parentId = null)
    {
        return $this->state(function (array $attributes) use ($parentId) {
            return [
                'parent_id' => $parentId ?? TdFolder::factory(),
            ];
        });
    }
    
    public function system()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_system' => true,
                'is_locked' => true,
            ];
        });
    }
    
    public function locked()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_locked' => true,
            ];
        });
    }
    
    public function starred()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_starred' => true,
            ];
        });
    }
    
    public function public()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_public' => true,
            ];
        });
    }
}

// ============================================================================
// 15. EVENT: TdFolderCreated.php
// Path: Modules/TerminalData/Events/TdFolderCreated.php
// ============================================================================

namespace Modules\TerminalData\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Modules\TerminalData\Entities\TdFolder;

class TdFolderCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $folder;
    
    public function __construct(TdFolder $folder)
    {
        $this->folder = $folder;
    }
}

// ============================================================================
// 16. LISTENER: UpdateFolderStats.php
// Path: Modules/TerminalData/Listeners/UpdateFolderStats.php
// ============================================================================

namespace Modules\TerminalData\Listeners;

use Modules\TerminalData\Events\TdFileUploaded;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateFolderStats implements ShouldQueue
{
    public function handle(TdFileUploaded $event): void
    {
        $file = $event->file;
        
        if ($file->folder) {
            $file->folder->updateStats();
        }
    }
}

// ============================================================================
// 17. OBSERVER: TdFolderObserver.php (Optional)
// Path: Modules/TerminalData/Observers/TdFolderObserver.php
// ============================================================================

namespace Modules\TerminalData\Observers;

use Modules\TerminalData\Entities\TdFolder;
use Modules\TerminalData\Events\TdFolderCreated;

class TdFolderObserver
{
    public function created(TdFolder $folder): void
    {
        event(new TdFolderCreated($folder));
    }
    
    public function updating(TdFolder $folder): void
    {
        // Log changes before update
        if ($folder->isDirty('name')) {
            \Log::info("Folder name changed from {$folder->getOriginal('name')} to {$folder->name}");
        }
    }
    
    public function deleted(TdFolder $folder): void
    {
        // Cleanup related data if needed
        \Log::info("Folder {$folder->name} deleted");
    }
}

// To register observer, add to TerminalDataServiceProvider:
// TdFolder::observe(TdFolderObserver::class);


// ============================================================================
// 18. SERVICE PROVIDER: TerminalDataServiceProvider.php
// Path: Modules/TerminalData/Providers/TerminalDataServiceProvider.php
// ============================================================================

namespace Modules\TerminalData\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\TerminalData\Entities\TdFolder;
use Modules\TerminalData\Policies\TdFolderPolicy;
use Modules\TerminalData\Observers\TdFolderObserver;

class TerminalDataServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'TerminalData';
    protected string $moduleNameLower = 'terminaldata';
    
    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->registerPolicies();
        $this->registerObservers();
    }
    
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        
        // Register Repository bindings
        $this->app->bind(
            \Modules\TerminalData\Repositories\TdFolderRepository::class,
            \Modules\TerminalData\Repositories\TdFolderRepository::class
        );
        
        // Register Service bindings
        $this->app->singleton(
            \Modules\TerminalData\Services\TdFolderService::class,
            function ($app) {
                return new \Modules\TerminalData\Services\TdFolderService(
                    $app->make(\Modules\TerminalData\Repositories\TdFolderRepository::class),
                    $app->make(\Modules\TerminalData\Services\TdActivityService::class)
                );
            }
        );
    }
    
    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), 
            $this->moduleNameLower
        );
    }
    
    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'Resources/views');
        
        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);
        
        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }
    
    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);
        
        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }
    
    /**
     * Register policies.
     */
    protected function registerPolicies(): void
    {
        Gate::policy(TdFolder::class, TdFolderPolicy::class);
    }
    
    /**
     * Register observers.
     */
    protected function registerObservers(): void
    {
        TdFolder::observe(TdFolderObserver::class);
    }
    
    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
    
    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}

// ============================================================================
// 19. ROUTE SERVICE PROVIDER: RouteServiceProvider.php
// Path: Modules/TerminalData/Providers/RouteServiceProvider.php
// ============================================================================

namespace Modules\TerminalData\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\TerminalData\Http\Controllers';
    
    /**
     * Called before routes are registered.
     */
    public function boot(): void
    {
        parent::boot();
    }
    
    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }
    
    /**
     * Define the "web" routes for the application.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('TerminalData', '/Routes/web.php'));
    }
    
    /**
     * Define the "api" routes for the application.
     */
    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('TerminalData', '/Routes/api.php'));
    }
}

// ============================================================================
// 20. CONFIG: config.php
// Path: Modules/TerminalData/Config/config.php
// ============================================================================

return [
    'name' => 'TerminalData',
    
    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'disk' => env('TD_STORAGE_DISK', 'local'),
        'path' => env('TD_STORAGE_PATH', 'terminal-data'),
        'max_file_size' => env('TD_MAX_FILE_SIZE', 10240), // KB (10MB default)
        'allowed_extensions' => [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 
            'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 
            'png', 'gif', 'zip', 'rar'
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Folder Configuration
    |--------------------------------------------------------------------------
    */
    'folder' => [
        'max_depth' => env('TD_MAX_FOLDER_DEPTH', 10),
        'default_color' => '#6B7280',
        'default_icon' => 'folder',
        'colors' => [
            '#EF4444', // Red
            '#F59E0B', // Orange
            '#10B981', // Green
            '#3B82F6', // Blue
            '#6366F1', // Indigo
            '#8B5CF6', // Purple
            '#EC4899', // Pink
            '#14B8A6', // Teal
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Version Configuration
    |--------------------------------------------------------------------------
    */
    'versioning' => [
        'enabled' => env('TD_VERSIONING_ENABLED', true),
        'max_versions' => env('TD_MAX_VERSIONS', 10),
        'auto_cleanup' => env('TD_AUTO_CLEANUP_VERSIONS', false),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Share Configuration
    |--------------------------------------------------------------------------
    */
    'share' => [
        'enabled' => env('TD_SHARE_ENABLED', true),
        'public_link_enabled' => env('TD_PUBLIC_LINK_ENABLED', true),
        'default_expiry_days' => env('TD_SHARE_EXPIRY_DAYS', 30),
        'require_password' => env('TD_SHARE_REQUIRE_PASSWORD', false),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Activity Log Configuration
    |--------------------------------------------------------------------------
    */
    'activity' => [
        'enabled' => env('TD_ACTIVITY_LOG_ENABLED', true),
        'retention_days' => env('TD_ACTIVITY_RETENTION_DAYS', 90),
        'track_views' => env('TD_TRACK_VIEWS', true),
        'track_downloads' => env('TD_TRACK_DOWNLOADS', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    */
    'search' => [
        'enabled' => env('TD_SEARCH_ENABLED', true),
        'use_elasticsearch' => env('TD_USE_ELASTICSEARCH', false),
        'min_search_length' => 3,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Document Numbering
    |--------------------------------------------------------------------------
    */
    'numbering' => [
        'enabled' => env('TD_NUMBERING_ENABLED', true),
        'format' => env('TD_NUMBER_FORMAT', '[TYPE]/[COUNTER]/[BIDANG]/[YEAR]'),
        'auto_increment' => env('TD_AUTO_INCREMENT', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Thumbnail Configuration
    |--------------------------------------------------------------------------
    */
    'thumbnail' => [
        'enabled' => env('TD_THUMBNAIL_ENABLED', true),
        'width' => 200,
        'height' => 200,
        'quality' => 80,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | OCR Configuration (for text extraction)
    |--------------------------------------------------------------------------
    */
    'ocr' => [
        'enabled' => env('TD_OCR_ENABLED', false),
        'supported_formats' => ['pdf', 'jpg', 'jpeg', 'png'],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'cache_enabled' => env('TD_CACHE_ENABLED', true),
        'cache_ttl' => env('TD_CACHE_TTL', 3600), // seconds
        'queue_enabled' => env('TD_QUEUE_ENABLED', true),
    ],
];

// ============================================================================
// 21. HELPER: TdFolderHelper.php
// Path: Modules/TerminalData/Helpers/TdFolderHelper.php
// ============================================================================

namespace Modules\TerminalData\Helpers;

use Modules\TerminalData\Entities\TdFolder;

class TdFolderHelper
{
    /**
     * Generate folder tree for dropdown/select
     */
    public static function getFolderTree(?string $excludeId = null): array
    {
        $folders = TdFolder::with('subfolders')
            ->whereNull('parent_id')
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->orderBy('name')
            ->get();
        
        return self::buildTree($folders, $excludeId);
    }
    
    /**
     * Build nested tree structure
     */
    private static function buildTree($folders, ?string $excludeId = null, int $level = 0): array
    {
        $tree = [];
        
        foreach ($folders as $folder) {
            if ($folder->id === $excludeId) {
                continue;
            }
            
            $tree[] = [
                'id' => $folder->id,
                'name' => str_repeat('— ', $level) . $folder->name,
                'level' => $level,
                'path' => $folder->path,
                'is_locked' => $folder->is_locked,
                'is_system' => $folder->is_system,
            ];
            
            if ($folder->subfolders->count() > 0) {
                $tree = array_merge(
                    $tree, 
                    self::buildTree($folder->subfolders, $excludeId, $level + 1)
                );
            }
        }
        
        return $tree;
    }
    
    /**
     * Get folder path as array
     */
    public static function getFolderPath(TdFolder $folder): array
    {
        return $folder->getBreadcrumb()->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->name,
        ])->toArray();
    }
    
    /**
     * Format file size to human readable
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Check if user can perform action on folder
     */
    public static function canPerformAction(TdFolder $folder, string $action, $user = null): bool
    {
        $user = $user ?? auth()->user();
        
        if (!$user) {
            return false;
        }
        
        return match($action) {
            'view' => $user->can('view', $folder),
            'edit' => $user->can('update', $folder),
            'delete' => $user->can('delete', $folder),
            'share' => $user->can('share', $folder),
            default => false,
        };
    }
    
    /**
     * Get folder icon based on name or type
     */
    public static function getFolderIcon(TdFolder $folder): string
    {
        if ($folder->icon) {
            return $folder->icon;
        }
        
        $name = strtolower($folder->name);
        
        return match(true) {
            str_contains($name, 'surat masuk') => 'inbox-arrow-down',
            str_contains($name, 'surat keluar') => 'paper-airplane',
            str_contains($name, 'arsip') => 'archive-box',
            str_contains($name, 'laporan') => 'document-chart-bar',
            str_contains($name, 'sk') => 'document-check',
            str_contains($name, 'keputusan') => 'clipboard-document-check',
            $folder->is_system => 'shield-check',
            default => 'folder',
        };
    }
    
    /**
     * Validate folder name
     */
    public static function validateFolderName(string $name): bool
    {
        // Check for invalid characters
        $invalidChars = ['/', '\\', ':', '*', '?', '"', '<', '>', '|'];
        
        foreach ($invalidChars as $char) {
            if (str_contains($name, $char)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Generate unique folder name if duplicate exists
     */
    public static function generateUniqueName(string $name, ?string $parentId = null): string
    {
        $originalName = $name;
        $counter = 1;
        
        while (TdFolder::where('name', $name)
            ->where('parent_id', $parentId)
            ->exists()) {
            $name = $originalName . ' (' . $counter . ')';
            $counter++;
        }
        
        return $name;
    }
}

// ============================================================================
// 22. MIDDLEWARE: CheckFolderAccess.php
// Path: Modules/TerminalData/Http/Middleware/CheckFolderAccess.php
// ============================================================================

namespace Modules\TerminalData\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\TerminalData\Entities\TdFolder;

class CheckFolderAccess
{
    public function handle(Request $request, Closure $next, string $permission = 'viewer')
    {
        $folder = $request->route('folder');
        
        if (!$folder instanceof TdFolder) {
            return response()->json([
                'success' => false,
                'message' => 'Folder tidak ditemukan'
            ], 404);
        }
        
        if (!$folder->canAccess(auth()->user(), $permission)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke folder ini'
            ], 403);
        }
        
        return $next($request);
    }
}

// ============================================================================
// 23. COMMAND: CleanupOldFolders.php
// Path: Modules/TerminalData/Console/CleanupOldFolders.php
// ============================================================================

namespace Modules\TerminalData\Console;

use Illuminate\Console\Command;
use Modules\TerminalData\Entities\TdFolder;
use Carbon\Carbon;

class CleanupOldFolders extends Command
{
    protected $signature = 'td:cleanup-folders {--days=30 : Days to keep in trash}';
    protected $description = 'Permanently delete folders that have been in trash for specified days';
    
    public function handle(): int
    {
        $days = $this->option('days');
        $date = Carbon::now()->subDays($days);
        
        $this->info("Cleaning up folders deleted before {$date->format('Y-m-d')}...");
        
        $folders = TdFolder::onlyTrashed()
            ->where('deleted_at', '<', $date)
            ->get();
        
        $count = 0;
        foreach ($folders as $folder) {
            $this->line("Permanently deleting: {$folder->name}");
            $folder->forceDelete();
            $count++;
        }
        
        $this->info("Permanently deleted {$count} folders.");
        
        return 0;
    }
}

// ============================================================================
// 24. COMMAND: CreateSystemFolders.php
// Path: Modules/TerminalData/Console/CreateSystemFolders.php
// ============================================================================

namespace Modules\TerminalData\Console;

use Illuminate\Console\Command;
use Modules\TerminalData\Services\TdFolderService;
use App\Models\MasterBidang;

class CreateSystemFolders extends Command
{
    protected $signature = 'td:create-system-folders';
    protected $description = 'Create system folders for all bidang';
    
    protected $folderService;
    
    public function __construct(TdFolderService $folderService)
    {
        parent::__construct();
        $this->folderService = $folderService;
    }
    
    public function handle(): int
    {
        $bidangs = MasterBidang::all();
        
        if ($bidangs->isEmpty()) {
            $this->error('No bidang found!');
            return 1;
        }
        
        $this->info('Creating system folders...');
        
        foreach ($bidangs as $bidang) {
            $this->line("Creating folder for: {$bidang->nama}");
            
            $folder = $this->folderService->createSystemFolder([
                'name' => $bidang->nama,
                'description' => "Folder utama untuk {$bidang->nama}",
                'bidang_id' => $bidang->id,
                'icon' => 'building-office',
                'color' => '#3B82F6',
                'created_by' => 1, // System user
            ]);
            
            // Create subfolders
            $subfolders = [
                'Surat Masuk' => 'inbox-arrow-down',
                'Surat Keluar' => 'paper-airplane',
                'SK & Keputusan' => 'document-check',
                'Laporan' => 'document-chart-bar',
            ];
            
            foreach ($subfolders as $name => $icon) {
                $this->folderService->create([
                    'parent_id' => $folder->id,
                    'name' => $name,
                    'bidang_id' => $bidang->id,
                    'icon' => $icon,
                    'created_by' => 1,
                ]);
            }
        }
        
        $this->info('System folders created successfully!');
        
        return 0;
    }
}

// Register commands in TerminalDataServiceProvider:
// protected $commands = [
//     Commands\CleanupOldFolders::class,
//     Commands\CreateSystemFolders::class,
// ];
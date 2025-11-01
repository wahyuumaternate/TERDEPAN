<?php

// ============================================================================
// 1. MIGRATION: create_td_files_table.php
// Path: Modules/TerminalData/Database/Migrations/xxxx_xx_xx_create_td_files_table.php
// ============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('td_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('folder_id')->index();
            $table->foreignId('bidang_id')->nullable()->constrained('master_bidang')->nullOnDelete();
            
            // File Basic Info
            $table->string('name');
            $table->string('original_name')->comment('Original filename when uploaded');
            $table->text('description')->nullable();
            
            // File Details
            $table->string('mime_type');
            $table->string('extension', 10);
            $table->unsignedBigInteger('size')->comment('File size in bytes');
            $table->string('storage_path')->comment('Path in storage');
            $table->string('hash', 64)->comment('SHA256 hash for integrity');
            $table->string('thumbnail_path')->nullable();
            
            // Document Specific
            $table->string('document_number')->nullable()->unique();
            $table->date('document_date')->nullable();
            $table->string('document_type')->nullable()->comment('Surat, SK, Memo, etc');
            $table->enum('status', ['draft', 'review', 'approved', 'final', 'archived'])->default('draft');
            
            // Versioning
            $table->unsignedInteger('version')->default(1);
            $table->uuid('original_file_id')->nullable()->comment('ID file asli untuk tracking versions');
            $table->boolean('is_latest_version')->default(true);
            $table->text('version_notes')->nullable();
            
            // Relations (Polymorphic)
            $table->string('attachable_type')->nullable()->index();
            $table->unsignedBigInteger('attachable_id')->nullable()->index();
            
            // Permissions & Status
            $table->boolean('is_public')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_locked')->default(false)->comment('Prevent editing/deletion');
            $table->boolean('is_encrypted')->default(false);
            
            // Stats
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('downloads')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamp('last_downloaded_at')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable()->comment('Custom attributes');
            $table->json('extracted_content')->nullable()->comment('OCR/extracted text for search');
            
            // Ownership
            $table->foreignId('created_by')->constrained('master_pegawai')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('master_pegawai')->restrictOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['folder_id', 'status']);
            $table->index(['original_file_id', 'version']);
            $table->index(['created_by', 'is_starred']);
            $table->index(['document_number', 'document_date']);
            $table->fullText(['name', 'description', 'original_name']);
            
            // Foreign keys
            $table->foreign('folder_id')->references('id')->on('td_folders')->cascadeOnDelete();
            $table->foreign('original_file_id')->references('id')->on('td_files')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('td_files');
    }
};

// ============================================================================
// 2. MODEL: TdFile.php
// Path: Modules/TerminalData/Entities/TdFile.php
// ============================================================================

namespace Modules\TerminalData\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\MasterPegawai;
use App\Models\MasterBidang;

class TdFile extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'td_files';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'folder_id',
        'bidang_id',
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
        return $query->where(function($q) use ($search) {
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
    
    // ==================== Helper Methods ====================
    
    public function getHumanSize()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
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
            'created_by' => auth()->id(),
        ]);
        
        $newFile->save();
        
        return $newFile;
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
        
        // Check folder access
        if ($this->folder->canAccess($user, $permission)) {
            return true;
        }
        
        // Check direct shares
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
        $newFile->created_by = auth()->id();
        $newFile->created_at = now();
        
        // Copy physical file
        $newPath = str_replace($this->id, $newFile->id, $this->storage_path);
        Storage::copy($this->storage_path, $newPath);
        $newFile->storage_path = $newPath;
        
        $newFile->save();
        
        return $newFile;
    }
}

// ============================================================================
// 3. CONTROLLER: TdFileController.php
// Path: Modules/TerminalData/Http/Controllers/Api/TdFileController.php
// ============================================================================

namespace Modules\TerminalData\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\TerminalData\Entities\TdFile;
use Modules\TerminalData\Http\Requests\StoreTdFileRequest;
use Modules\TerminalData\Http\Requests\UpdateTdFileRequest;
use Modules\TerminalData\Http\Resources\TdFileResource;
use Modules\TerminalData\Services\TdFileService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TdFileController extends Controller
{
    protected $fileService;
    
    public function __construct(TdFileService $fileService)
    {
        $this->fileService = $fileService;
        
        $this->middleware('auth:sanctum');
        $this->middleware('can:view,file')->only(['show', 'download']);
        $this->middleware('can:update,file')->only(['update']);
        $this->middleware('can:delete,file')->only(['destroy']);
    }
    
    /**
     * Display a listing of files
     */
    public function index(Request $request): JsonResponse
    {
        $files = TdFile::query()
            ->with(['creator', 'folder', 'bidang', 'tags'])
            ->when($request->folder_id, fn($q) => $q->where('folder_id', $request->folder_id))
            ->when($request->bidang_id, fn($q) => $q->where('bidang_id', $request->bidang_id))
            ->when($request->status, fn($q) => $q->byStatus($request->status))
            ->when($request->document_type, fn($q) => $q->byType($request->document_type))
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->starred, fn($q) => $q->starred())
            ->when($request->latest_only, fn($q) => $q->latestVersions())
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 15);
        
        return response()->json([
            'success' => true,
            'data' => TdFileResource::collection($files),
            'meta' => [
                'total' => $files->total(),
                'per_page' => $files->perPage(),
                'current_page' => $files->currentPage(),
            ]
        ]);
    }
    
    /**
     * Store a newly created file
     */
    public function store(StoreTdFileRequest $request): JsonResponse
    {
        try {
            $file = $this->fileService->upload(
                $request->file('file'),
                $request->validated()
            );
            
            return response()->json([
                'success' => true,
                'message' => 'File berhasil diupload',
                'data' => new TdFileResource($file)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload file: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the specified file
     */
    public function show(TdFile $file): JsonResponse
    {
        $file->load(['creator', 'folder', 'bidang', 'tags', 'versions']);
        $file->incrementViews();
        
        return response()->json([
            'success' => true,
            'data' => new TdFileResource($file)
        ]);
    }
    
    /**
     * Update the specified file
     */
    public function update(UpdateTdFileRequest $request, TdFile $file): JsonResponse
    {
        try {
            $file = $this->fileService->update($file, $request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'File berhasil diupdate',
                'data' => new TdFileResource($file)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update file: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove the specified file
     */
    public function destroy(TdFile $file): JsonResponse
    {
        try {
            $this->fileService->delete($file);
            
            return response()->json([
                'success' => true,
                'message' => 'File berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus file: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Download file
     */
    public function download(TdFile $file): StreamedResponse
    {
        $file->incrementDownloads();
        
        return Storage::download(
            $file->storage_path,
            $file->original_name
        );
    }
    
    /**
     * Preview file (inline display)
     */
    public function preview(TdFile $file)
    {
        $file->incrementViews();
        
        return Storage::response($file->storage_path, $file->original_name, [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'inline; filename="' . $file->original_name . '"'
        ]);
    }
    
    /**
     * Upload new version
     */
    public function uploadVersion(Request $request, TdFile $file): JsonResponse
    {
        $request->validate([
            'file' => 'required|file',
            'version_notes' => 'nullable|string|max:1000'
        ]);
        
        try {
            $newVersion = $this->fileService->uploadVersion(
                $file,
                $request->file('file'),
                $request->version_notes
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Versi baru berhasil diupload',
                'data' => new TdFileResource($newVersion)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get file versions
     */
    public function versions(TdFile $file): JsonResponse
    {
        $versions = $file->versions()->with('creator')->get();
        
        return response()->json([
            'success' => true,
            'data' => TdFileResource::collection($versions)
        ]);
    }
    
    /**
     * Move file to another folder
     */
    public function move(Request $request, TdFile $file): JsonResponse
    {
        $request->validate([
            'folder_id' => 'required|uuid|exists:td_folders,id'
        ]);
        
        try {
            $this->fileService->move($file, $request->folder_id);
            
            return response()->json([
                'success' => true,
                'message' => 'File berhasil dipindahkan',
                'data' => new TdFileResource($file->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Duplicate file
     */
    public function duplicate(Request $request, TdFile $file): JsonResponse
    {
        $request->validate([
            'folder_id' => 'nullable|uuid|exists:td_folders,id',
            'name' => 'nullable|string|max:255'
        ]);
        
        try {
            $duplicate = $this->fileService->duplicate(
                $file,
                $request->folder_id,
                $request->name
            );
            
            return response()->json([
                'success' => true,
                'message' => 'File berhasil diduplikat',
                'data' => new TdFileResource($duplicate)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Toggle star status
     */
    public function toggleStar(TdFile $file): JsonResponse
    {
        $file->update(['is_starred' => !$file->is_starred]);
        
        return response()->json([
            'success' => true,
            'message' => $file->is_starred ? 'File ditandai' : 'Tanda dihapus',
            'data' => new TdFileResource($file)
        ]);
    }
}

// ============================================================================
// 4. REQUEST: StoreTdFileRequest.php
// Path: Modules/TerminalData/Http/Requests/StoreTdFileRequest.php
// ============================================================================

namespace Modules\TerminalData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTdFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        $maxSize = config('terminaldata.storage.max_file_size', 10240); // KB
        $allowedExtensions = implode(',', config('terminaldata.storage.allowed_extensions', []));
        
        return [
            'file' => "required|file|max:{$maxSize}|mimes:{$allowedExtensions}",
            'folder_id' => 'required|uuid|exists:td_folders,id',
            'bidang_id' => 'nullable|exists:master_bidang,id',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'document_number' => 'nullable|string|max:100|unique:td_files,document_number',
            'document_date' => 'nullable|date',
            'document_type' => 'nullable|string|max:100',
            'status' => 'nullable|in:draft,review,approved,final,archived',
            'is_public' => 'boolean',
            'attachable_type' => 'nullable|string|max:255',
            'attachable_id' => 'nullable|integer',
            'metadata' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:td_tags,id',
        ];
    }
    
    public function messages(): array
    {
        return [
            'file.required' => 'File wajib diupload',
            'file.max' => 'Ukuran file maksimal :max KB',
            'file.mimes' => 'Format file tidak didukung',
            'folder_id.required' => 'Folder tujuan wajib dipilih',
            'folder_id.exists' => 'Folder tidak ditemukan',
            'document_number.unique' => 'Nomor dokumen sudah digunakan',
        ];
    }
}

// ============================================================================
// 5. REQUEST: UpdateTdFileRequest.php
// Path: Modules/TerminalData/Http/Requests/UpdateTdFileRequest.php
// ============================================================================

namespace Modules\TerminalData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTdFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        $fileId = $this->route('file')->id;
        
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'document_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('td_files', 'document_number')->ignore($fileId)
            ],
            'document_date' => 'nullable|date',
            'document_type' => 'nullable|string|max:100',
            'status' => 'nullable|in:draft,review,approved,final,archived',
            'is_public' => 'boolean',
            'is_starred' => 'boolean',
            'metadata' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:td_tags,id',
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.max' => 'Nama file maksimal 255 karakter',
            'document_number.unique' => 'Nomor dokumen sudah digunakan',
        ];
    }
}

// ============================================================================
// 6. RESOURCE: TdFileResource.php
// Path: Modules/TerminalData/Http/Resources/TdFileResource.php
// ============================================================================

namespace Modules\TerminalData\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TdFileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'folder_id' => $this->folder_id,
            'bidang_id' => $this->bidang_id,
            
            // Basic Info
            'name' => $this->name,
            'original_name' => $this->original_name,
            'description' => $this->description,
            
            // File Details
            'mime_type' => $this->mime_type,
            'extension' => $this->extension,
            'size' => $this->size,
            'human_size' => $this->getHumanSize(),
            'hash' => $this->hash,
            
            // Document Info
            'document_number' => $this->document_number,
            'document_date' => $this->document_date?->format('Y-m-d'),
            'document_type' => $this->document_type,
            'status' => $this->status,
            
            // Versioning
            'version' => $this->version,
            'original_file_id' => $this->original_file_id,
            'is_latest_version' => $this->is_latest_version,
            'version_notes' => $this->version_notes,
            
            // Polymorphic
            'attachable_type' => $this->attachable_type,
            'attachable_id' => $this->attachable_id,
            
            // Flags
            'is_public' => $this->is_public,
            'is_starred' => $this->is_starred,
            'is_locked' => $this->is_locked,
            'is_encrypted' => $this->is_encrypted,
            
            // Stats
            'views' => $this->views,
            'downloads' => $this->downloads,
            'last_viewed_at' => $this->last_viewed_at?->format('Y-m-d H:i:s'),
            'last_downloaded_at' => $this->last_downloaded_at?->format('Y-m-d H:i:s'),
            
            // Metadata
            'metadata' => $this->metadata,
            
            // URLs
            'download_url' => $this->getDownloadUrl(),
            'preview_url' => $this->getPreviewUrl(),
            
            // Type helpers
            'is_image' => $this->isImage(),
            'is_pdf' => $this->isPdf(),
            'is_document' => $this->isDocument(),
            
            // Relationships
            'creator' => [
                'id' => $this->creator->id,
                'nama' => $this->creator->nama,
                'nip' => $this->creator->nip,
            ],
            'folder' => [
                'id' => $this->folder->id,
                'name' => $this->folder->name,
                'path' => $this->folder->path,
            ],
            'bidang' => $this->when($this->bidang, [
                'id' => $this->bidang?->id,
                'nama' => $this->bidang?->nama,
            ]),
            
            // Load when included
            'versions' => TdFileResource::collection($this->whenLoaded('versions')),
            'tags' => TdTagResource::collection($this->whenLoaded('tags')),
            'comments' => TdCommentResource::collection($this->whenLoaded('comments')),
            
            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            
            // Computed
            'can_edit' => $this->when(auth()->check(), fn() => 
                !$this->is_locked && ($this->created_by === auth()->id() || auth()->user()->hasRole('admin'))
            ),
            'can_delete' => $this->when(auth()->check(), fn() => 
                !$this->is_locked && ($this->created_by === auth()->id() || auth()->user()->hasRole('admin'))
            ),
            'can_download' => $this->when(auth()->check(), fn() => 
                $this->canAccess(auth()->user(), 'viewer')
            ),
        ];
    }
}

// ============================================================================
// 7. SERVICE: TdFileService.php
// Path: Modules/TerminalData/Services/TdFileService.php
// ============================================================================

namespace Modules\TerminalData\Services;

use Modules\TerminalData\Entities\TdFile;
use Modules\TerminalData\Entities\TdFolder;
use Modules\TerminalData\Repositories\TdFileRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class TdFileService
{
    protected $fileRepository;
    protected $activityService;
    protected $numberingService;
    
    public function __construct(
        TdFileRepository $fileRepository,
        TdActivityService $activityService,
        TdNumberingService $numberingService
    ) {
        $this->fileRepository = $fileRepository;
        $this->activityService = $activityService;
        $this->numberingService = $numberingService;
    }
    
    /**
     * Upload file
     */
    public function upload(UploadedFile $uploadedFile, array $data): TdFile
    {
        DB::beginTransaction();
        
        try {
            // Validate folder access
            $folder = TdFolder::findOrFail($data['folder_id']);
            
            if (!$folder->canAccess(auth()->user(), 'editor')) {
                throw new \Exception('Anda tidak memiliki akses untuk upload ke folder ini');
            }
            
            // Generate storage path
            $storagePath = $this->generateStoragePath($uploadedFile);
            
            // Store file
            $path = $uploadedFile->storeAs(
                dirname($storagePath),
                basename($storagePath),
                config('terminaldata.storage.disk', 'local')
            );
            
            // Prepare file data
            $fileData = [
                'folder_id' => $data['folder_id'],
                'bidang_id' => $data['bidang_id'] ?? $folder->bidang_id,
                'name' => $data['name'] ?? pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME),
                'original_name' => $uploadedFile->getClientOriginalName(),
                'description' => $data['description'] ?? null,
                'mime_type' => $uploadedFile->getMimeType(),
                'extension' => $uploadedFile->getClientOriginalExtension(),
                'size' => $uploadedFile->getSize(),
                'storage_path' => $path,
                'hash' => hash_file('sha256', $uploadedFile->getRealPath()),
                'document_number' => $data['document_number'] ?? null,
                'document_date' => $data['document_date'] ?? null,
                'document_type' => $data['document_type'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'is_public' => $data['is_public'] ?? false,
                'attachable_type' => $data['attachable_type'] ?? null,
                'attachable_id' => $data['attachable_id'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'created_by' => auth()->id(),
            ];
            
            // Create file record
            $file = $this->fileRepository->create($fileData);
            
            // Generate thumbnail for images
            if ($file->isImage() && config('terminaldata.thumbnail.enabled', true)) {
                $this->generateThumbnail($file);
            }
            
            // Auto-generate document number if needed
            if (empty($file->document_number) && config('terminaldata.numbering.auto_increment', true)) {
                $file->document_number = $this->numberingService->generate(
                    $file->bidang_id,
                    $file->document_type ?? 'DOC'
                );
                $file->save();
            }
            
            // Attach tags
            if (!empty($data['tags'])) {
                $file->tags()->sync($data['tags']);
            }
            
            // Log activity
            $this->activityService->log($file, 'upload', 'File diupload');
            
            DB::commit();
            
            return $file->fresh(['creator', 'folder', 'bidang', 'tags']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if exists
            if (isset($path) && Storage::exists($path)) {
                Storage::delete($path);
            }
            
            Log::error('Error uploading file: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update file metadata
     */
    public function update(TdFile $file, array $data): TdFile
    {
        DB::beginTransaction();
        
        try {
            // Check if locked
            if ($file->is_locked && !auth()->user()->hasRole('admin')) {
                throw new \Exception('File terkunci dan tidak bisa diubah');
            }
            
            // Set updater
            $data['updated_by'] = auth()->id();
            
            // Store old values for activity log
            $oldValues = $file->only(['name', 'description', 'status', 'document_number']);
            
            // Update file
            $file = $this->fileRepository->update($file, $data);
            
            // Update tags
            if (isset($data['tags'])) {
                $file->tags()->sync($data['tags']);
            }
            
            // Log activity
            $this->activityService->log($file, 'edit', 'File diupdate', [
                'old' => $oldValues,
                'new' => $file->only(['name', 'description', 'status', 'document_number'])
            ]);
            
            DB::commit();
            
            return $file->fresh(['creator', 'folder', 'bidang', 'tags']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating file: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Delete file
     */
    public function delete(TdFile $file): bool
    {
        DB::beginTransaction();
        
        try {
            // Check if locked
            if ($file->is_locked && !auth()->user()->hasRole('admin')) {
                throw new \Exception('File terkunci dan tidak bisa dihapus');
            }
            
            // Log activity before delete
            $this->activityService->log($file, 'delete', 'File dihapus');
            
            // Delete file (physical file deleted in model event)
            $result = $this->fileRepository->delete($file);
            
            DB::commit();
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting file: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Upload new version
     */
    public function uploadVersion(TdFile $file, UploadedFile $uploadedFile, ?string $notes = null): TdFile
    {
        DB::beginTransaction();
        
        try {
            // Check versioning enabled
            if (!config('terminaldata.versioning.enabled', true)) {
                throw new \Exception('Versioning tidak diaktifkan');
            }
            
            // Check max versions
            $maxVersions = config('terminaldata.versioning.max_versions', 10);
            if ($file->versions()->count() >= $maxVersions) {
                throw new \Exception("Maksimal {$maxVersions} versi");
            }
            
            // Generate storage path
            $storagePath = $this->generateStoragePath($uploadedFile);
            
            // Store file
            $path = $uploadedFile->storeAs(
                dirname($storagePath),
                basename($storagePath),
                config('terminaldata.storage.disk', 'local')
            );
            
            // Create new version
            $newVersion = $file->createVersion($path, $notes);
            
            // Generate thumbnail for images
            if ($newVersion->isImage() && config('terminaldata.thumbnail.enabled', true)) {
                $this->generateThumbnail($newVersion);
            }
            
            // Log activity
            $this->activityService->log($newVersion, 'upload', "Versi {$newVersion->version} diupload");
            
            DB::commit();
            
            return $newVersion->fresh(['creator', 'folder']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($path) && Storage::exists($path)) {
                Storage::delete($path);
            }
            
            Log::error('Error uploading version: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Move file to another folder
     */
    public function move(TdFile $file, string $newFolderId): TdFile
    {
        DB::beginTransaction();
        
        try {
            $oldFolder = $file->folder;
            $newFolder = TdFolder::findOrFail($newFolderId);
            
            // Check access
            if (!$newFolder->canAccess(auth()->user(), 'editor')) {
                throw new \Exception('Anda tidak memiliki akses ke folder tujuan');
            }
            
            $file->folder_id = $newFolderId;
            $file->save();
            
            // Update folder stats
            $oldFolder->updateStats();
            $newFolder->updateStats();
            
            // Log activity
            $this->activityService->log($file, 'move', 'File dipindahkan', [
                'from' => $oldFolder->name,
                'to' => $newFolder->name
            ]);
            
            DB::commit();
            
            return $file->fresh(['creator', 'folder']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error moving file: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Duplicate file
     */
    public function duplicate(TdFile $file, ?string $newFolderId = null, ?string $newName = null): TdFile
    {
        DB::beginTransaction();
        
        try {
            $duplicate = $file->duplicate($newFolderId, $newName);
            
            // Copy tags
            $duplicate->tags()->sync($file->tags->pluck('id'));
            
            // Log activity
            $this->activityService->log($duplicate, 'create', 'File diduplikat dari ' . $file->name);
            
            DB::commit();
            
            return $duplicate;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating file: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generate storage path
     */
    protected function generateStoragePath(UploadedFile $file): string
    {
        $basePath = config('terminaldata.storage.path', 'terminal-data');
        $date = now()->format('Y/m');
        $uuid = Str::uuid();
        $extension = $file->getClientOriginalExtension();
        
        return "{$basePath}/{$date}/{$uuid}.{$extension}";
    }
    
    /**
     * Generate thumbnail
     */
    protected function generateThumbnail(TdFile $file): void
    {
        try {
            $width = config('terminaldata.thumbnail.width', 200);
            $height = config('terminaldata.thumbnail.height', 200);
            $quality = config('terminaldata.thumbnail.quality', 80);
            
            $image = Image::make(Storage::path($file->storage_path));
            $image->fit($width, $height);
            
            $thumbnailPath = str_replace(
                '.' . $file->extension,
                '_thumb.' . $file->extension,
                $file->storage_path
            );
            
            Storage::put($thumbnailPath, (string) $image->encode(null, $quality));
            
            $file->thumbnail_path = $thumbnailPath;
            $file->save();
            
        } catch (\Exception $e) {
            Log::warning('Failed to generate thumbnail: ' . $e->getMessage());
        }
    }
    
    /**
     * Search files
     */
    public function search(string $query, array $filters = [])
    {
        return $this->fileRepository->search($query, $filters);
    }
    
    /**
     * Get recent files
     */
    public function getRecentFiles($userId, int $limit = 10)
    {
        return $this->fileRepository->getRecentFiles($userId, $limit);
    }
}

// ============================================================================
// 8. REPOSITORY: TdFileRepository.php
// Path: Modules/TerminalData/Repositories/TdFileRepository.php
// ============================================================================

namespace Modules\TerminalData\Repositories;

use Modules\TerminalData\Entities\TdFile;
use Illuminate\Database\Eloquent\Collection;

class TdFileRepository
{
    /**
     * Create file
     */
    public function create(array $data): TdFile
    {
        return TdFile::create($data);
    }
    
    /**
     * Update file
     */
    public function update(TdFile $file, array $data): TdFile
    {
        $file->update($data);
        return $file;
    }
    
    /**
     * Delete file
     */
    public function delete(TdFile $file): bool
    {
        return $file->delete();
    }
    
    /**
     * Find file by ID
     */
    public function find(string $id): ?TdFile
    {
        return TdFile::find($id);
    }
    
    /**
     * Search files
     */
    public function search(string $query, array $filters = [])
    {
        $queryBuilder = TdFile::query()
            ->with(['creator', 'folder', 'bidang', 'tags'])
            ->latestVersions()
            ->search($query);
        
        if (isset($filters['folder_id'])) {
            $queryBuilder->where('folder_id', $filters['folder_id']);
        }
        
        if (isset($filters['bidang_id'])) {
            $queryBuilder->where('bidang_id', $filters['bidang_id']);
        }
        
        if (isset($filters['status'])) {
            $queryBuilder->where('status', $filters['status']);
        }
        
        if (isset($filters['document_type'])) {
            $queryBuilder->where('document_type', $filters['document_type']);
        }
        
        if (isset($filters['created_by'])) {
            $queryBuilder->where('created_by', $filters['created_by']);
        }
        
        if (isset($filters['extension'])) {
            $queryBuilder->where('extension', $filters['extension']);
        }
        
        return $queryBuilder->get();
    }
    
    /**
     * Get recent files
     */
    public function getRecentFiles($userId, int $limit = 10): Collection
    {
        return TdFile::where('created_by', $userId)
            ->latestVersions()
            ->with(['folder', 'tags'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get starred files
     */
    public function getStarredFiles($userId): Collection
    {
        return TdFile::where('created_by', $userId)
            ->starred()
            ->latestVersions()
            ->with(['folder', 'tags'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }
    
    /**
     * Get files by folder
     */
    public function getFilesByFolder(string $folderId): Collection
    {
        return TdFile::where('folder_id', $folderId)
            ->latestVersions()
            ->with(['creator', 'tags'])
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Get files by status
     */
    public function getFilesByStatus(string $status): Collection
    {
        return TdFile::where('status', $status)
            ->latestVersions()
            ->with(['creator', 'folder'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Get duplicate files (by hash)
     */
    public function findDuplicates(string $hash): Collection
    {
        return TdFile::where('hash', $hash)
            ->with(['folder', 'creator'])
            ->get();
    }
}

<?php

// ============================================================================
// 9. POLICY: TdFilePolicy.php
// Path: Modules/TerminalData/Policies/TdFilePolicy.php
// ============================================================================

namespace Modules\TerminalData\Policies;

use App\Models\User;
use Modules\TerminalData\Entities\TdFile;
use Illuminate\Auth\Access\HandlesAuthorization;

class TdFilePolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine if user can view any files
     */
    public function viewAny(User $user): bool
    {
        return true;
    }
    
    /**
     * Determine if user can view the file
     */
    public function view(User $user, TdFile $file): bool
    {
        // Owner can always view
        if ($file->created_by === $user->id) {
            return true;
        }
        
        // Public files
        if ($file->is_public) {
            return true;
        }
        
        // Check folder access
        if ($file->folder->canAccess($user, 'viewer')) {
            return true;
        }
        
        // Check direct shares
        return $file->canAccess($user, 'viewer');
    }
    
    /**
     * Determine if user can create files
     */
    public function create(User $user): bool
    {
        return true;
    }
    
    /**
     * Determine if user can update the file
     */
    public function update(User $user, TdFile $file): bool
    {
        // Locked files can only be edited by admin
        if ($file->is_locked && !$user->hasRole('admin')) {
            return false;
        }
        
        // Owner can update
        if ($file->created_by === $user->id) {
            return true;
        }
        
        // Check if user has editor access through folder or direct shares
        return $file->canAccess($user, 'editor');
    }
    
    /**
     * Determine if user can delete the file
     */
    public function delete(User $user, TdFile $file): bool
    {
        // Locked files can only be deleted by admin
        if ($file->is_locked && !$user->hasRole('admin')) {
            return false;
        }
        
        // Owner can delete
        if ($file->created_by === $user->id) {
            return true;
        }
        
        // Admins can delete
        return $user->hasRole('admin');
    }
    
    /**
     * Determine if user can download the file
     */
    public function download(User $user, TdFile $file): bool
    {
        return $this->view($user, $file);
    }
    
    /**
     * Determine if user can share the file
     */
    public function share(User $user, TdFile $file): bool
    {
        // Owner can share
        if ($file->created_by === $user->id) {
            return true;
        }
        
        // Check if user has share permission through existing shares
        $share = $file->shares()
            ->where('user_id', $user->id)
            ->first();
            
        return $share && $share->can_share;
    }
}

// ============================================================================
// 10. SEEDER: TdFileSeeder.php
// Path: Modules/TerminalData/Database/Seeders/TdFileSeeder.php
// ============================================================================

namespace Modules\TerminalData\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Modules\TerminalData\Entities\TdFile;
use Modules\TerminalData\Entities\TdFolder;
use App\Models\MasterPegawai;
use Illuminate\Support\Str;

class TdFileSeeder extends Seeder
{
    public function run(): void
    {
        $pegawai = MasterPegawai::first();
        $folders = TdFolder::whereNotNull('parent_id')->get();
        
        if (!$pegawai || $folders->isEmpty()) {
            $this->command->warn('No pegawai or folders found. Skipping file seeder.');
            return;
        }
        
        // Sample file types
        $fileTypes = [
            ['extension' => 'pdf', 'mime' => 'application/pdf', 'type' => 'Surat'],
            ['extension' => 'docx', 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'type' => 'SK'],
            ['extension' => 'xlsx', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'type' => 'Laporan'],
            ['extension' => 'jpg', 'mime' => 'image/jpeg', 'type' => 'Foto'],
        ];
        
        $statuses = ['draft', 'review', 'approved', 'final', 'archived'];
        
        foreach ($folders->take(5) as $folder) {
            // Create 3-5 files per folder
            $fileCount = rand(3, 5);
            
            for ($i = 1; $i <= $fileCount; $i++) {
                $fileType = $fileTypes[array_rand($fileTypes)];
                $status = $statuses[array_rand($statuses)];
                
                // Create dummy file content
                $content = "This is a dummy file content for testing purposes.\n";
                $content .= "File created at: " . now()->toDateTimeString() . "\n";
                $content .= "Folder: {$folder->name}\n";
                
                $uuid = Str::uuid();
                $filename = "{$uuid}.{$fileType['extension']}";
                $path = "terminal-data/sample/{$filename}";
                
                // Store dummy file
                Storage::put($path, $content);
                
                TdFile::create([
                    'folder_id' => $folder->id,
                    'bidang_id' => $folder->bidang_id,
                    'name' => "Sample {$fileType['type']} {$i}",
                    'original_name' => "sample_{$i}.{$fileType['extension']}",
                    'description' => "This is a sample {$fileType['type']} document for testing",
                    'mime_type' => $fileType['mime'],
                    'extension' => $fileType['extension'],
                    'size' => strlen($content),
                    'storage_path' => $path,
                    'hash' => hash('sha256', $content),
                    'document_number' => $this->generateDocNumber($folder->bidang_id, $fileType['type']),
                    'document_date' => now()->subDays(rand(1, 30)),
                    'document_type' => $fileType['type'],
                    'status' => $status,
                    'is_public' => rand(0, 10) > 7, // 30% chance public
                    'is_starred' => rand(0, 10) > 8, // 20% chance starred
                    'metadata' => [
                        'author' => $pegawai->nama,
                        'keywords' => ['sample', 'test', $fileType['type']],
                    ],
                    'created_by' => $pegawai->id,
                ]);
            }
        }
        
        $this->command->info('File seeder completed successfully!');
    }
    
    private function generateDocNumber($bidangId, $type): string
    {
        $year = date('Y');
        $counter = rand(1, 999);
        return sprintf('%s/%03d/BDG-%d/%s', strtoupper($type), $counter, $bidangId, $year);
    }
}

// ============================================================================
// 11. ROUTES: api.php (add to existing)
// Path: Modules/TerminalData/Routes/api.php
// ============================================================================

use Illuminate\Support\Facades\Route;
use Modules\TerminalData\Http\Controllers\Api\TdFileController;

Route::middleware(['auth:sanctum'])->prefix('terminal-data')->group(function () {
    
    // File Routes
    Route::prefix('files')->name('td.files.')->group(function () {
        // CRUD Operations
        Route::get('/', [TdFileController::class, 'index'])->name('index');
        Route::post('/', [TdFileController::class, 'store'])->name('store');
        Route::get('/{file}', [TdFileController::class, 'show'])->name('show');
        Route::put('/{file}', [TdFileController::class, 'update'])->name('update');
        Route::delete('/{file}', [TdFileController::class, 'destroy'])->name('destroy');
        
        // File Actions
        Route::get('/{file}/download', [TdFileController::class, 'download'])->name('download');
        Route::get('/{file}/preview', [TdFileController::class, 'preview'])->name('preview');
        Route::post('/{file}/upload-version', [TdFileController::class, 'uploadVersion'])->name('upload-version');
        Route::get('/{file}/versions', [TdFileController::class, 'versions'])->name('versions');
        Route::post('/{file}/move', [TdFileController::class, 'move'])->name('move');
        Route::post('/{file}/duplicate', [TdFileController::class, 'duplicate'])->name('duplicate');
        Route::post('/{file}/toggle-star', [TdFileController::class, 'toggleStar'])->name('toggle-star');
    });
});

// ============================================================================
// 12. FEATURE TEST: TdFileTest.php
// Path: Modules/TerminalData/Tests/Feature/TdFileTest.php
// ============================================================================

namespace Modules\TerminalData\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\TerminalData\Entities\TdFile;
use Modules\TerminalData\Entities\TdFolder;
use App\Models\MasterPegawai;
use App\Models\MasterBidang;
use App\Models\User;

class TdFileTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    protected $user;
    protected $pegawai;
    protected $folder;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('local');
        
        $this->user = User::factory()->create();
        $this->pegawai = MasterPegawai::factory()->create([
            'user_id' => $this->user->id
        ]);
        $this->folder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
    }
    
    /** @test */
    public function it_can_list_files()
    {
        TdFile::factory()->count(5)->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id
        ]);
        
        $response = $this->actingAs($this->user)
            ->getJson('/api/terminal-data/files');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'size',
                        'extension'
                    ]
                ]
            ]);
    }
    
    /** @test */
    public function it_can_upload_file()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/terminal-data/files', [
                'file' => $file,
                'folder_id' => $this->folder->id,
                'name' => 'Test Document',
                'description' => 'Test description',
            ]);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'name', 'extension']
            ]);
        
        $this->assertDatabaseHas('td_files', [
            'name' => 'Test Document',
            'folder_id' => $this->folder->id
        ]);
        
        Storage::disk('local')->assertExists(
            TdFile::where('name', 'Test Document')->first()->storage_path
        );
    }
    
    /** @test */
    public function it_validates_file_upload()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/terminal-data/files', [
                'folder_id' => $this->folder->id,
            ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }
    
    /** @test */
    public function it_can_show_file()
    {
        $file = TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id
        ]);
        
        $response = $this->actingAs($this->user)
            ->getJson("/api/terminal-data/files/{$file->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $file->id,
                    'name' => $file->name
                ]
            ]);
    }
    
    /** @test */
    public function it_increments_views_when_showing_file()
    {
        $file = TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id
        ]);
        
        $this->assertEquals(0, $file->views);
        
        $this->actingAs($this->user)
            ->getJson("/api/terminal-data/files/{$file->id}");
        
        $file->refresh();
        $this->assertEquals(1, $file->views);
    }
    
    /** @test */
    public function it_can_update_file()
    {
        $file = TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id
        ]);
        
        $response = $this->actingAs($this->user)
            ->putJson("/api/terminal-data/files/{$file->id}", [
                'name' => 'Updated File Name',
                'description' => 'Updated description'
            ]);
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('td_files', [
            'id' => $file->id,
            'name' => 'Updated File Name'
        ]);
    }
    
    /** @test */
    public function it_cannot_update_locked_file()
    {
        $file = TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id,
            'is_locked' => true
        ]);
        
        $response = $this->actingAs($this->user)
            ->putJson("/api/terminal-data/files/{$file->id}", [
                'name' => 'New Name'
            ]);
        
        $response->assertStatus(500);
    }
    
    /** @test */
    public function it_can_delete_file()
    {
        $file = TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id
        ]);
        
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/terminal-data/files/{$file->id}");
        
        $response->assertStatus(200);
        
        $this->assertSoftDeleted('td_files', [
            'id' => $file->id
        ]);
    }
    
    /** @test */
    public function it_can_download_file()
    {
        $file = TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id
        ]);
        
        Storage::put($file->storage_path, 'file content');
        
        $response = $this->actingAs($this->user)
            ->get("/api/terminal-data/files/{$file->id}/download");
        
        $response->assertStatus(200);
        
        $file->refresh();
        $this->assertEquals(1, $file->downloads);
    }
    
    /** @test */
    public function it_can_upload_new_version()
    {
        $file = TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id,
            'version' => 1
        ]);
        
        $newFile = UploadedFile::fake()->create('document-v2.pdf', 100);
        
        $response = $this->actingAs($this->user)
            ->postJson("/api/terminal-data/files/{$file->id}/upload-version", [
                'file' => $newFile,
                'version_notes' => 'Updated version'
            ]);
        
        $response->assertStatus(200);
        
        $file->refresh();
        $this->assertFalse($file->is_latest_version);
        
        $newVersion = TdFile::where('original_file_id', $file->id)->first();
        $this->assertEquals(2, $newVersion->version);
        $this->assertTrue($newVersion->is_latest_version);
    }
    
    /** @test */
    public function it_can_get_file_versions()
    {
        $file = TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id,
            'version' => 1
        ]);
        
        // Create version 2
        TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id,
            'original_file_id' => $file->id,
            'version' => 2
        ]);
        
        $response = $this->actingAs($this->user)
            ->getJson("/api/terminal-data/files/{$file->id}/versions");
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
    
    /** @test */
    public function it_can_move_file()
    {
        $file = TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id
        ]);
        
        $newFolder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $response = $this->actingAs($this->user)
            ->postJson("/api/terminal-data/files/{$file->id}/move", [
                'folder_id' => $newFolder->id
            ]);
        
        $response->assertStatus(200);
        
        $file->refresh();
        $this->assertEquals($newFolder->id, $file->folder_id);
    }
    
    /** @test */
    public function it_can_duplicate_file()
    {
        $file = TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id,
            'name' => 'Original File'
        ]);
        
        Storage::put($file->storage_path, 'file content');
        
        $response = $this->actingAs($this->user)
            ->postJson("/api/terminal-data/files/{$file->id}/duplicate", [
                'name' => 'Copied File'
            ]);
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('td_files', [
            'name' => 'Copied File',
            'folder_id' => $file->folder_id
        ]);
    }
    
    /** @test */
    public function it_can_toggle_star()
    {
        $file = TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id,
            'is_starred' => false
        ]);
        
        $response = $this->actingAs($this->user)
            ->postJson("/api/terminal-data/files/{$file->id}/toggle-star");
        
        $response->assertStatus(200);
        
        $file->refresh();
        $this->assertTrue($file->is_starred);
    }
    
    /** @test */
    public function it_updates_folder_stats_on_upload()
    {
        $this->assertEquals(0, $this->folder->total_files);
        
        $file = UploadedFile::fake()->create('document.pdf', 100);
        
        $this->actingAs($this->user)
            ->postJson('/api/terminal-data/files', [
                'file' => $file,
                'folder_id' => $this->folder->id,
            ]);
        
        $this->folder->refresh();
        $this->assertEquals(1, $this->folder->total_files);
    }
    
    /** @test */
    public function it_filters_files_by_status()
    {
        TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id,
            'status' => 'draft'
        ]);
        
        TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id,
            'status' => 'final'
        ]);
        
        $response = $this->actingAs($this->user)
            ->getJson('/api/terminal-data/files?status=draft');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}

// ============================================================================
// 13. UNIT TEST: TdFileServiceTest.php
// Path: Modules/TerminalData/Tests/Unit/TdFileServiceTest.php
// ============================================================================

namespace Modules\TerminalData\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\TerminalData\Entities\TdFile;
use Modules\TerminalData\Entities\TdFolder;
use Modules\TerminalData\Services\TdFileService;
use App\Models\MasterPegawai;
use App\Models\User;

class TdFileServiceTest extends TestCase
{
    use RefreshDatabase;
    
    protected $fileService;
    protected $user;
    protected $pegawai;
    protected $folder;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('local');
        
        $this->user = User::factory()->create();
        $this->pegawai = MasterPegawai::factory()->create([
            'user_id' => $this->user->id
        ]);
        $this->folder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        $this->actingAs($this->user);
        
        $this->fileService = app(TdFileService::class);
    }
    
    /** @test */
    public function it_can_upload_file()
    {
        $uploadedFile = UploadedFile::fake()->create('document.pdf', 100);
        
        $data = [
            'folder_id' => $this->folder->id,
            'name' => 'Test Document',
            'description' => 'Test description',
        ];
        
        $file = $this->fileService->upload($uploadedFile, $data);
        
        $this->assertInstanceOf(TdFile::class, $file);
        $this->assertEquals('Test Document', $file->name);
        $this->assertEquals($this->pegawai->id, $file->created_by);
        Storage::assertExists($file->storage_path);
    }
    
    /** @test */
    public function it_generates_hash_on_upload()
    {
        $uploadedFile = UploadedFile::fake()->create('document.pdf', 100);
        
        $file = $this->fileService->upload($uploadedFile, [
            'folder_id' => $this->folder->id,
        ]);
        
        $this->assertNotEmpty($file->hash);
        $this->assertEquals(64, strlen($file->hash)); // SHA256
    }
    
    /** @test */
    public function it_can_update_file()
    {
        $file = TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id
        ]);
        
        $updated = $this->fileService->update($file, [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ]);
        
        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals('Updated Description', $updated->description);
    }
    
    /** @test */
    public function it_cannot_update_locked_file()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File terkunci');
        
        $file = TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id,
            'is_locked' => true
        ]);
        
        $this->fileService->update($file, ['name' => 'New Name']);
    }
    
    /** @test */
    public function it_can_delete_file()
    {
        $file = TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id
        ]);
        
        Storage::put($file->storage_path, 'content');
        
        $result = $this->fileService->delete($file);
        
        $this->assertTrue($result);
        $this->assertSoftDeleted('td_files', ['id' => $file->id]);
        Storage::assertMissing($file->storage_path);
    }
    
    /** @test */
    public function it_can_upload_new_version()
    {
        $file = TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id,
            'version' => 1
        ]);
        
        Storage::put($file->storage_path, 'content');
        
        $newFile = UploadedFile::fake()->create('document-v2.pdf', 100);
        
        $newVersion = $this->fileService->uploadVersion($file, $newFile, 'Version 2');
        
        $this->assertEquals(2, $newVersion->version);
        $this->assertTrue($newVersion->is_latest_version);
        
        $file->refresh();
        $this->assertFalse($file->is_latest_version);
    }
    
    /** @test */
    public function it_can_move_file()
    {
        $file = TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id
        ]);
        
        $newFolder = TdFolder::factory()->create([
            'created_by' => $this->pegawai->id
        ]);
        
        $moved = $this->fileService->move($file, $newFolder->id);
        
        $this->assertEquals($newFolder->id, $moved->folder_id);
    }
    
    /** @test */
    public function it_can_duplicate_file()
    {
        $file = TdFile::factory()->create([
            'folder_id' => $this->folder->id,
            'created_by' => $this->pegawai->id,
            'name' => 'Original'
        ]);
        
        Storage::put($file->storage_path, 'content');
        
        $duplicate = $this->fileService->duplicate($file, null, 'Copy');
        
        $this->assertEquals('Copy', $duplicate->name);
        $this->assertNotEquals($file->id, $duplicate->id);
        Storage::assertExists($duplicate->storage_path);
    }
    
    /** @test */
    public function it_updates_folder_stats()
    {
        $uploadedFile = UploadedFile::fake()->create('document.pdf', 100);
        
        $this->fileService->upload($uploadedFile, [
            'folder_id' => $this->folder->id,
        ]);
        
        $this->folder->refresh();
        $this->assertEquals(1, $this->folder->total_files);
    }
}
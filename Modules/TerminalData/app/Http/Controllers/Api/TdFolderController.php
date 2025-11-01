<?php

namespace Modules\TerminalData\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TerminalData\Models\TdFolder;
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

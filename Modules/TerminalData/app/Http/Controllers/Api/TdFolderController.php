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
    public function __construct(
        protected TdFolderService $folderService
    ) {
        $this->middleware('auth:sanctum');
    }
    /**
     * Display a listing of folders
     */
    public function index(Request $request): JsonResponse
    {
        try {
            /** @var \App\Models\MasterPegawai $user */
            $user = $request->user();

            // Build filters from request
            $filters = [
                'level' => $request->input('level', 0), // Default to level 0 (bidang)
                'bidang_id' => $request->input('bidang_id'),
                'search' => $request->input('search'),
                'is_starred' => $request->input('starred'),
                'is_public' => $request->input('is_public'),
                'sort_by' => $request->input('sort_by', 'name'),
                'sort_order' => $request->input('sort_order', 'asc'),
            ];

            // Remove null values
            $filters = array_filter($filters, fn($value) => $value !== null);

            // Get folders from service
            $folders = $this->folderService->getRootFolders($user);

            return response()->json([
                'success' => true,
                'data' => TdFolderResource::collection($folders),
                'message' => 'Data folder berhasil dimuat'
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created folder
     */
    public function store(StoreTdFolderRequest $request): JsonResponse
    {
        try {
            /** @var \App\Models\MasterPegawai $user */
            $user = $request->user();

            $folder = $this->folderService->createFolder($request->validated(), $user);

            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil dibuat',
                'data' => new TdFolderResource($folder)
            ], 201);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
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
    public function show($folderId): JsonResponse
    {
        try {
            /** @var \App\Models\MasterPegawai $user */
            $user = request()->user();

            // Get folder by ID using service
            $folder = $this->folderService->getFolderById($folderId, $user);

            if (!$folder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Folder tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new TdFolderResource($folder)
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get children folders of a folder
     */
    public function children($folderId): JsonResponse
    {
        try {
            /** @var \App\Models\MasterPegawai $user */
            $user = request()->user();

            // Get folder by ID using service
            $folder = $this->folderService->getFolderById($folderId, $user);

            if (!$folder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Folder tidak ditemukan'
                ], 404);
            }

            // Get subfolders
            $subfolders = $folder->subfolders()->with(['creator', 'bidang'])->get();

            return response()->json(TdFolderResource::collection($subfolders));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified folder
     */
    public function update(UpdateTdFolderRequest $request, TdFolder $folder): JsonResponse
    {
        try {
            /** @var \App\Models\MasterPegawai $user */
            $user = $request->user();
            
            $folder = $this->folderService->updateFolder($folder, $request->validated(), $user);

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
    public function destroy(TdFolder $folder, Request $request): JsonResponse
    {
        try {
            /** @var \App\Models\MasterPegawai $user */
            $user = $request->user();

            // Check if folder has subfolders
            if ($folder->subfolders()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus folder yang masih memiliki subfolder'
                ], 400);
            }

            // Check if folder has files
            if ($folder->files()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus folder yang masih memiliki file'
                ], 400);
            }

            // Delete folder via service
            $this->folderService->deleteFolder($folder, $user);

            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil dipindahkan ke sampah'
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
            $this->folderService->moveFolder($folder, $request->parent_id, $request->user());

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

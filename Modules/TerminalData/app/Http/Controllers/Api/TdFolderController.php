<?php

namespace Modules\TerminalData\Http\Controllers\Api;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\TerminalData\Http\Requests\StoreTdFolderRequest;
use Modules\TerminalData\Http\Requests\UpdateTdFolderRequest;
use Modules\TerminalData\Http\Resources\TdFolderResource;
use Modules\TerminalData\Models\TdFolder;
use Modules\TerminalData\Services\TdFolderService;

/**
 * @OA\Tag(
 *     name="Folders",
 *     description="API folder Terminal Data. Struktur folder bertingkat (parent_id), scoping akses per bidang lewat TdFolderPolicy."
 * )
 *
 * @OA\Get(
 *     path="/folders",
 *     security={{"bearerAuth":{}}},
 *     tags={"Folders"},
 *     summary="List folder (root jika parent_id kosong, atau anak dari parent_id)",
 *
 *     @OA\Parameter(name="parent_id", in="query", @OA\Schema(type="string", format="uuid")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki akses")
 * )
 *
 * @OA\Post(
 *     path="/folders",
 *     security={{"bearerAuth":{}}},
 *     tags={"Folders"},
 *     summary="Buat folder baru. Untuk selain ADMIN/KABAN/SEKBAN, bidang_id otomatis mengikuti bidang user (atau bidang parent_id)",
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"name"},
 *
 *             @OA\Property(property="parent_id", type="string", format="uuid", nullable=true),
 *             @OA\Property(property="name", type="string", example="Laporan Triwulan"),
 *             @OA\Property(property="description", type="string", nullable=true),
 *             @OA\Property(property="bidang_id", type="integer", nullable=true, description="Diabaikan untuk role selain ADMIN/KABAN/SEKBAN"),
 *             @OA\Property(property="color", type="string", example="#4f46e5"),
 *             @OA\Property(property="icon", type="string", example="folder"),
 *             @OA\Property(property="is_public", type="boolean"),
 *             @OA\Property(property="is_starred", type="boolean")
 *         )
 *     ),
 *
 *     @OA\Response(response=201, description="Created"),
 *     @OA\Response(response=403, description="Hanya boleh membuat folder di bidang sendiri"),
 *     @OA\Response(response=422, description="Validasi gagal")
 * )
 *
 * @OA\Get(
 *     path="/folders/{folder}",
 *     security={{"bearerAuth":{}}},
 *     tags={"Folders"},
 *     summary="Detail folder",
 *
 *     @OA\Parameter(name="folder", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Folder tidak ditemukan")
 * )
 *
 * @OA\Put(
 *     path="/folders/{folder}",
 *     security={{"bearerAuth":{}}},
 *     tags={"Folders"},
 *     summary="Update nama/deskripsi/visibilitas folder",
 *
 *     @OA\Parameter(name="folder", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"name"},
 *
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="description", type="string", nullable=true),
 *             @OA\Property(property="is_public", type="boolean")
 *         )
 *     ),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin"),
 *     @OA\Response(response=422, description="Validasi gagal")
 * )
 *
 * @OA\Delete(
 *     path="/folders/{folder}",
 *     security={{"bearerAuth":{}}},
 *     tags={"Folders"},
 *     summary="Pindahkan folder ke sampah (soft delete). Ditolak jika masih berisi subfolder/file",
 *
 *     @OA\Parameter(name="folder", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=400, description="Folder masih memiliki subfolder atau file")
 * )
 *
 * @OA\Get(
 *     path="/folders/{folder}/children",
 *     security={{"bearerAuth":{}}},
 *     tags={"Folders"},
 *     summary="List subfolder langsung",
 *
 *     @OA\Parameter(name="folder", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki akses"),
 *     @OA\Response(response=404, description="Folder tidak ditemukan")
 * )
 *
 * @OA\Get(
 *     path="/folders/{folder}/breadcrumb",
 *     security={{"bearerAuth":{}}},
 *     tags={"Folders"},
 *     summary="Breadcrumb folder (dari root sampai folder ini)",
 *
 *     @OA\Parameter(name="folder", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Get(
 *     path="/folders/{folder}/stats",
 *     security={{"bearerAuth":{}}},
 *     tags={"Folders"},
 *     summary="Statistik folder (jumlah file, subfolder, ukuran total)",
 *
 *     @OA\Parameter(name="folder", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Post(
 *     path="/folders/{folder}/move",
 *     security={{"bearerAuth":{}}},
 *     tags={"Folders"},
 *     summary="Pindahkan folder ke parent lain (null berarti dipindahkan ke root)",
 *
 *     @OA\Parameter(name="folder", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *
 *     @OA\RequestBody(
 *
 *         @OA\JsonContent(@OA\Property(property="parent_id", type="string", format="uuid", nullable=true))
 *     ),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=400, description="Gagal memindahkan folder")
 * )
 *
 * @OA\Post(
 *     path="/folders/{folder}/toggle-star",
 *     security={{"bearerAuth":{}}},
 *     tags={"Folders"},
 *     summary="Tandai/batalkan tanda favorit folder",
 *
 *     @OA\Parameter(name="folder", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Post(
 *     path="/folders/{folder}/restore",
 *     security={{"bearerAuth":{}}},
 *     tags={"Folders"},
 *     summary="Pulihkan folder dari sampah",
 *
 *     @OA\Parameter(name="folder", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin")
 * )
 *
 * @OA\Delete(
 *     path="/folders/{folder}/force-delete",
 *     security={{"bearerAuth":{}}},
 *     tags={"Folders"},
 *     summary="Hapus folder permanen (harus sudah di sampah)",
 *
 *     @OA\Parameter(name="folder", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin")
 * )
 */
class TdFolderController extends Controller
{
    use AuthorizesRequests;

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
        /** @var \App\Models\User $user */
        $user = $request->user();
        $parentId = $request->get('parent_id');

        try {
            // Authorize viewAny folders
            $this->authorize('viewAny', TdFolder::class);
            if ($parentId === null || $parentId === 'null') {
                // Get root folders with permission filtering
                $folders = TdFolder::whereNull('parent_id')
                    ->forUser($user)
                    ->withCount('subfolders')
                    ->orderBy('name')
                    ->get();
            } else {
                // Get subfolders with permission filtering
                $folders = TdFolder::where('parent_id', $parentId)
                    ->forUser($user)
                    ->withCount('subfolders')
                    ->orderBy('name')
                    ->get();
            }

            return response()->json($folders);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            Log::error('Error loading folders: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat folder',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created folder
     */
    public function store(StoreTdFolderRequest $request): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = $request->user();

            $folder = $this->folderService->createFolder($request->validated(), $user);

            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil dibuat',
                'data' => new TdFolderResource($folder),
            ], 201);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat folder: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified folder
     */
    public function show($folderId): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = request()->user();

            // Get folder by ID using service
            $folder = $this->folderService->getFolderById($folderId, $user);

            if (! $folder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Folder tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new TdFolderResource($folder),
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get children folders of a folder
     */
    public function children($folderId): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = request()->user();

            // Get folder by ID using service
            $folder = $this->folderService->getFolderById($folderId, $user);

            if (! $folder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Folder tidak ditemukan',
                ], 404);
            }

            // Authorize view access
            $this->authorize('view', $folder);

            // Get subfolders with permission filtering
            $subfolders = $folder->subfolders()
                ->forUser($user)
                ->with(['creator', 'bidang'])
                ->get();

            return response()->json(TdFolderResource::collection($subfolders));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified folder
     */
    public function update(UpdateTdFolderRequest $request, TdFolder $folder): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = $request->user();

            $folder = $this->folderService->updateFolder($folder, $request->validated(), $user);

            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil diupdate',
                'data' => new TdFolderResource($folder),
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update folder: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified folder
     */
    public function destroy(TdFolder $folder, Request $request): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = $request->user();

            // Check if folder has subfolders
            if ($folder->subfolders()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus folder yang masih memiliki subfolder',
                ], 400);
            }

            // Check if folder has files
            if ($folder->files()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus folder yang masih memiliki file',
                ], 400);
            }

            // Delete folder via service
            $this->folderService->deleteFolder($folder, $user);

            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil dipindahkan ke sampah',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus folder: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get folder breadcrumb
     */
    public function breadcrumb(TdFolder $folder): JsonResponse
    {
        // Authorize view
        $this->authorize('view', $folder);

        $breadcrumb = $folder->getBreadcrumb();

        return response()->json([
            'success' => true,
            'data' => TdFolderResource::collection($breadcrumb),
        ]);
    }

    /**
     * Move folder to another parent
     */
    public function move(Request $request, TdFolder $folder): JsonResponse
    {
        $request->validate([
            'parent_id' => 'nullable|uuid|exists:td_folders,id',
        ]);

        try {
            $this->folderService->moveFolder($folder, $request->parent_id, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil dipindahkan',
                'data' => new TdFolderResource($folder->fresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Toggle star status
     */
    public function toggleStar(TdFolder $folder): JsonResponse
    {
        // Authorize view (user must be able to see the folder to star it)
        $this->authorize('view', $folder);

        $folder->update(['is_starred' => ! $folder->is_starred]);

        return response()->json([
            'success' => true,
            'message' => $folder->is_starred ? 'Folder ditandai' : 'Tanda dihapus',
            'data' => new TdFolderResource($folder),
        ]);
    }

    /**
     * Get folder statistics
     */
    public function stats(TdFolder $folder): JsonResponse
    {
        // Authorize view
        $this->authorize('view', $folder);

        return response()->json([
            'success' => true,
            'data' => [
                'total_files' => $folder->total_files,
                'total_subfolders' => $folder->total_subfolders,
                'total_size' => $folder->total_size,
                'human_size' => $folder->getHumanSize(),
                'level' => $folder->level,
            ],
        ]);
    }

    /**
     * Restore folder from trash
     */
    public function restore($folderId): JsonResponse
    {
        try {
            $folder = TdFolder::onlyTrashed()->findOrFail($folderId);

            // Authorize restore
            $this->authorize('restore', $folder);

            // Restore folder
            $folder->restore();

            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil dipulihkan',
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk memulihkan folder ini',
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan folder: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Permanently delete folder
     */
    public function forceDelete($folderId): JsonResponse
    {
        try {
            $folder = TdFolder::onlyTrashed()->findOrFail($folderId);

            // Authorize force delete
            $this->authorize('forceDelete', $folder);

            // Permanently delete folder
            $folder->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil dihapus permanen',
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus folder ini secara permanen',
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus folder permanen: '.$e->getMessage(),
            ], 500);
        }
    }
}

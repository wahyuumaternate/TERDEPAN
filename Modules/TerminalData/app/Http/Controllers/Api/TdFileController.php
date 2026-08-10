<?php

namespace Modules\TerminalData\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TerminalData\Classes\Services\TdActivityService;
use Modules\TerminalData\Http\Resources\TdFileResource;
use Modules\TerminalData\Models\TdFile;
use Modules\TerminalData\Models\TdFolder;
use Modules\TerminalData\Services\FileManagerService;

/**
 * @OA\Tag(
 *     name="Files",
 *     description="API file Terminal Data. Maks. 100MB per file, tipe: dokumen (PDF/Word/Excel/PowerPoint) atau gambar (JPG/PNG/GIF/BMP/SVG/WEBP)."
 * )
 *
 * @OA\Post(
 *     path="/files/upload",
 *     security={{"bearerAuth":{}}},
 *     tags={"Files"},
 *     summary="Upload file ke folder",
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *
 *             @OA\Schema(
 *                 required={"folder_id","file"},
 *
 *                 @OA\Property(property="folder_id", type="string", format="uuid"),
 *                 @OA\Property(property="file", type="string", format="binary")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin upload ke folder ini"),
 *     @OA\Response(response=422, description="Validasi gagal (tipe/ukuran file tidak sesuai)")
 * )
 *
 * @OA\Get(
 *     path="/files/search",
 *     security={{"bearerAuth":{}}},
 *     tags={"Files"},
 *     summary="Cari file lintas folder (nama/deskripsi, tipe, pemilik, rentang hari terakhir)",
 *
 *     @OA\Parameter(name="q", in="query", @OA\Schema(type="string")),
 *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string")),
 *     @OA\Parameter(name="creator_id", in="query", @OA\Schema(type="integer")),
 *     @OA\Parameter(name="days", in="query", @OA\Schema(type="integer")),
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=20)),
 *
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Get(
 *     path="/files/{file}/download",
 *     security={{"bearerAuth":{}}},
 *     tags={"Files"},
 *     summary="Download file (attachment)",
 *
 *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *
 *     @OA\Response(response=200, description="Isi file"),
 *     @OA\Response(response=404, description="File tidak ditemukan")
 * )
 *
 * @OA\Get(
 *     path="/files/{file}/serve",
 *     security={{"bearerAuth":{}}},
 *     tags={"Files"},
 *     summary="Tampilkan file inline (preview di browser)",
 *
 *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *
 *     @OA\Response(response=200, description="Isi file"),
 *     @OA\Response(response=404, description="File tidak ditemukan")
 * )
 *
 * @OA\Put(
 *     path="/files/{file}",
 *     security={{"bearerAuth":{}}},
 *     tags={"Files"},
 *     summary="Ubah nama file",
 *
 *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(required={"name"}, @OA\Property(property="name", type="string"))
 *     ),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin"),
 *     @OA\Response(response=422, description="Validasi gagal")
 * )
 *
 * @OA\Delete(
 *     path="/files/{file}",
 *     security={{"bearerAuth":{}}},
 *     tags={"Files"},
 *     summary="Pindahkan file ke sampah (soft delete)",
 *
 *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin")
 * )
 *
 * @OA\Post(
 *     path="/files/{file}/restore",
 *     security={{"bearerAuth":{}}},
 *     tags={"Files"},
 *     summary="Pulihkan file dari sampah",
 *
 *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin")
 * )
 *
 * @OA\Delete(
 *     path="/files/{file}/force-delete",
 *     security={{"bearerAuth":{}}},
 *     tags={"Files"},
 *     summary="Hapus file permanen (harus sudah di sampah)",
 *
 *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Tidak memiliki izin")
 * )
 *
 * @OA\Post(
 *     path="/trash/empty",
 *     security={{"bearerAuth":{}}},
 *     tags={"Files"},
 *     summary="Kosongkan sampah (hapus permanen banyak file/folder sekaligus)",
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"items"},
 *
 *             @OA\Property(
 *                 property="items",
 *                 type="array",
 *
 *                 @OA\Items(
 *
 *                     @OA\Property(property="id", type="string", format="uuid"),
 *                     @OA\Property(property="type", type="string", enum={"file","folder"})
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=200, description="OK")
 * )
 */
class TdFileController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected TdActivityService $activityService,
        protected FileManagerService $fileManager
    ) {
        $this->middleware('auth:sanctum');
    }

    /**
     * Upload file(s) to folder
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'folder_id' => 'required|uuid|exists:td_folders,id',
            'file' => [
                'required',
                'file',
                'max:102400', // 100MB max
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,bmp,svg,webp',
            ],
        ], [
            'file.mimes' => 'File harus berupa dokumen (PDF, Word, Excel, PowerPoint) atau gambar (JPG, PNG, GIF, dll)',
            'file.max' => 'Ukuran file maksimal 100MB',
        ]);

        try {
            /** @var \App\Models\User $user */
            $user = $request->user();

            // Check if folder exists
            $folder = TdFolder::findOrFail($request->folder_id);

            // Check upload permission dengan folder context
            $this->authorize('upload', [TdFile::class, $folder]);

            // Get uploaded file
            $uploadedFile = $request->file('file');
            $originalName = $uploadedFile->getClientOriginalName();

            $storagePath = "terminal-data/{$folder->bidang_id}/{$folder->id}";
            $stored = $this->fileManager->store($uploadedFile, $storagePath);

            // Create file record
            $file = TdFile::create([
                'folder_id' => $folder->id,
                'bidang_id' => $folder->bidang_id,
                'sub_bidang_id' => $folder->sub_bidang_id,
                'name' => pathinfo($originalName, PATHINFO_FILENAME),
                'original_name' => $originalName,
                'storage_path' => $stored['path'],
                'disk' => $stored['disk'],
                'extension' => $stored['extension'],
                'mime_type' => $stored['mime_type'],
                'size' => $stored['size'],
                'hash' => $stored['hash'],
                'version' => 1,
                'is_latest_version' => true,
                'created_by' => $user->id,
            ]);

            // Update folder stats
            $folder->updateStats();

            $this->activityService->log($file, 'uploaded', $user, "mengunggah \"{$file->original_name}\"");

            return response()->json([
                'success' => true,
                'message' => 'File berhasil diupload',
                'data' => [
                    'id' => $file->id,
                    'name' => $file->name,
                    'size' => round($file->size / 1024, 2).' KB',
                ],
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk upload file ke folder ini. '.
                    'Pastikan folder sesuai dengan bidang/sub bidang Anda atau Anda adalah pemilik folder.',
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload file: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download file
     */
    public function download(Request $request, $fileId)
    {
        try {
            $file = TdFile::findOrFail($fileId);

            if (! $request->hasValidSignature()) {
                $this->authorize('download', $file);
            }

            if ($request->user()) {
                $this->activityService->log($file, 'downloaded', $request->user(), "mengunduh \"{$file->original_name}\"");
            }

            // Use original_name for download to preserve extension and full name
            return $this->fileManager->download($file->disk ?? config('filesystems.default'), $file->storage_path, $file->original_name);
        } catch (\Exception $e) {
            abort(500, 'Gagal mendownload file: '.$e->getMessage());
        }
    }

    /**
     * Update file name
     */
    public function update(Request $request, $fileId): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $file = TdFile::findOrFail($fileId);

            // Authorize update
            $this->authorize('update', $file);

            // Update file name
            $oldName = $file->name;
            $file->name = $request->name;
            $file->save();

            $this->activityService->log($file, 'renamed', $request->user(), "mengganti nama \"{$oldName}\" menjadi \"{$file->name}\"", [
                'old_name' => $oldName,
                'new_name' => $file->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nama file berhasil diubah',
                'data' => [
                    'id' => $file->id,
                    'name' => $file->name,
                ],
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengubah file ini',
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah nama file: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Serve file from private storage
     */
    public function serve(Request $request, $fileId)
    {
        $file = TdFile::findOrFail($fileId);

        if (! $request->hasValidSignature()) {
            $this->authorize('view', $file);
        }

        return $this->fileManager->serveInline(
            $file->disk ?? config('filesystems.default'),
            $file->storage_path,
            $file->original_name,
            $file->mime_type
        );
    }

    /**
     * Delete file (soft delete - move to trash)
     */
    public function destroy($fileId): JsonResponse
    {
        try {
            $file = TdFile::findOrFail($fileId);

            // Authorize delete
            $this->authorize('delete', $file);

            $this->activityService->log($file, 'trashed', request()->user(), "memindahkan \"{$file->original_name}\" ke sampah");

            // Soft delete (move to trash)
            $file->delete();

            // Update folder stats
            if ($file->folder) {
                $file->folder->updateStats();
            }

            return response()->json([
                'success' => true,
                'message' => 'File berhasil dipindahkan ke sampah',
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus file. '.
                    'File di folder Eviden Kinerja tidak dapat dihapus atau Anda tidak memiliki izin.',
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memindahkan file ke sampah: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore file from trash
     */
    public function restore($fileId): JsonResponse
    {
        try {
            $file = TdFile::onlyTrashed()->findOrFail($fileId);

            // Authorize restore (using delete permission as proxy)
            $this->authorize('restore', $file);

            $this->activityService->log($file, 'restored', request()->user(), "memulihkan \"{$file->original_name}\" dari sampah");

            // Restore file
            $file->restore();

            // Update folder stats
            if ($file->folder) {
                $file->folder->updateStats();
            }

            return response()->json([
                'success' => true,
                'message' => 'File berhasil dipulihkan',
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk memulihkan file ini',
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan file: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Permanently delete file
     */
    public function forceDelete($fileId): JsonResponse
    {
        try {
            $file = TdFile::onlyTrashed()->findOrFail($fileId);

            // Authorize force delete (using delete permission as proxy)
            $this->authorize('forceDelete', $file);

            $this->activityService->log($file, 'force_deleted', request()->user(), "menghapus permanen \"{$file->original_name}\"");

            $disk = $file->disk ?? config('filesystems.default');
            $this->fileManager->deletePhysical($file->storage_path, $disk);
            $this->fileManager->deletePhysical($file->thumbnail_path, $disk);

            // Permanently delete from database
            $file->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'File berhasil dihapus permanen',
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus file ini secara permanen',
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus file permanen: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Empty trash - delete multiple items permanently
     */
    public function emptyTrash(Request $request): JsonResponse
    {
        try {
            $items = $request->input('items', []);
            $deletedFiles = 0;
            $deletedFolders = 0;

            foreach ($items as $item) {
                if ($item['type'] === 'file') {
                    $file = TdFile::onlyTrashed()->find($item['id']);
                    if ($file) {
                        $disk = $file->disk ?? config('filesystems.default');
                        $this->fileManager->deletePhysical($file->storage_path, $disk);
                        $this->fileManager->deletePhysical($file->thumbnail_path, $disk);
                        $file->forceDelete();
                        $deletedFiles++;
                    }
                } elseif ($item['type'] === 'folder') {
                    $folder = \Modules\TerminalData\Models\TdFolder::onlyTrashed()->find($item['id']);
                    if ($folder) {
                        $folder->forceDelete();
                        $deletedFolders++;
                    }
                }
            }

            $message = [];
            if ($deletedFiles > 0) {
                $message[] = "$deletedFiles file";
            }
            if ($deletedFolders > 0) {
                $message[] = "$deletedFolders folder";
            }

            return response()->json([
                'success' => true,
                'message' => implode(' dan ', $message).' berhasil dihapus permanen',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengosongkan sampah: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cari file lintas folder — sebelumnya scope-scope ini (scopeSearch, scopeByType,
     * scopeOwnedBy, scopeRecentlyUploaded) sudah ada di model tapi tidak ada endpoint
     * yang memanggilnya, jadi satu-satunya cara pengguna menemukan file adalah
     * membuka folder satu per satu secara manual.
     */
    public function search(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TdFile::class);

        $files = TdFile::query()
            ->with(['folder:id,name,path', 'creator:id,nama'])
            ->when($request->filled('q'), fn ($q) => $q->search($request->string('q')))
            ->when($request->filled('type'), fn ($q) => $q->byType($request->string('type')))
            ->when($request->filled('creator_id'), fn ($q) => $q->ownedBy($request->integer('creator_id')))
            ->when($request->filled('days'), fn ($q) => $q->recentlyUploaded($request->integer('days')))
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $files,
        ]);
    }

    /**
     * Detail file + riwayat aktivitas terakhir — dipakai panel info (Detail + Aktivitas) di UI.
     */
    public function detail($fileId): JsonResponse
    {
        $file = TdFile::with(['bidang', 'subBidang', 'folder:id,name,path', 'creator', 'updater'])->findOrFail($fileId);

        $this->authorize('view', $file);

        $activities = $file->activities()
            ->with('user:id,nama')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($activity) => [
                'action' => $activity->action,
                'description' => $activity->description,
                'user_nama' => $activity->user?->nama,
                'created_at' => $activity->created_at->format('Y-m-d H:i:s'),
            ]);

        return response()->json([
            'success' => true,
            'data' => new TdFileResource($file),
            'activities' => $activities,
        ]);
    }
}

<?php

namespace Modules\TerminalData\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\TerminalData\Models\TdFile;
use Modules\TerminalData\Models\TdFolder;

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

    public function __construct()
    {
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

            // Get file info BEFORE moving (important!)
            $originalName = $uploadedFile->getClientOriginalName();
            $fileSize = $uploadedFile->getSize();
            $mimeType = $uploadedFile->getMimeType();
            $extension = $uploadedFile->getClientOriginalExtension();

            // Generate unique filename
            $filename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)).'_'.time().'.'.$extension;

            // Define storage path
            $storagePath = "terminal-data/{$folder->bidang_id}/{$folder->id}";

            // Store file directly
            $path = $uploadedFile->storeAs($storagePath, $filename);

            // Calculate file hash
            $hash = hash_file('sha256', $uploadedFile->getRealPath());

            // Create file record
            $file = TdFile::create([
                'folder_id' => $folder->id,
                'bidang_id' => $folder->bidang_id,
                'sub_bidang_id' => $folder->sub_bidang_id,
                'name' => pathinfo($originalName, PATHINFO_FILENAME),
                'original_name' => $originalName,
                'storage_path' => $path,
                'extension' => $extension,
                'mime_type' => $mimeType,
                'size' => $fileSize,
                'hash' => $hash,
                'version' => 1,
                'is_latest_version' => true,
                'created_by' => $user->id,
            ]);

            // Update folder stats
            $folder->updateStats();

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
    public function download($fileId)
    {
        try {
            $file = TdFile::findOrFail($fileId);

            // Authorize download
            $this->authorize('download', $file);

            // Check if file exists in storage
            if (! $file->storage_path || ! Storage::exists($file->storage_path)) {
                abort(404, 'File tidak ditemukan');
            }

            // Use original_name for download to preserve extension and full name
            return Storage::download($file->storage_path, $file->original_name);
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
            $file->name = $request->name;
            $file->save();

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
    public function serve($fileId)
    {
        try {
            $file = TdFile::findOrFail($fileId);

            // Authorize view
            $this->authorize('view', $file);

            // Check if file exists in storage (local disk = storage/app/private)
            if (! Storage::exists($file->storage_path)) {
                abort(404, 'File tidak ditemukan di storage');
            }

            // Get file from private storage
            $filePath = Storage::path($file->storage_path);

            // Check if file physically exists
            if (! file_exists($filePath)) {
                abort(404, 'File tidak ditemukan');
            }

            // Get mime type from extension
            $extension = strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'webp' => 'image/webp',
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'ppt' => 'application/vnd.ms-powerpoint',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ];

            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

            // Return file response with appropriate headers
            return response()->file($filePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="'.$file->original_name.'"',
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        } catch (\Exception $e) {
            Log::error('Error serving file: '.$e->getMessage());
            abort(404, 'File tidak dapat diakses: '.$e->getMessage());
        }
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

            // Delete physical file from storage
            if (Storage::exists($file->storage_path)) {
                Storage::delete($file->storage_path);
            }

            // Delete thumbnail if exists
            if ($file->thumbnail_path && Storage::exists($file->thumbnail_path)) {
                Storage::delete($file->thumbnail_path);
            }

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
                        // Delete physical file
                        if (Storage::exists($file->storage_path)) {
                            Storage::delete($file->storage_path);
                        }
                        if ($file->thumbnail_path && Storage::exists($file->thumbnail_path)) {
                            Storage::delete($file->thumbnail_path);
                        }
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
}

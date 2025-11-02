<?php

namespace Modules\TerminalData\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\TerminalData\Jobs\ProcessFileUpload;
use Modules\TerminalData\Models\TdFile;
use Modules\TerminalData\Models\TdFolder;

class TdFileController extends Controller
{
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
                'max:51200', // 50MB max
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,bmp,svg,webp'
            ],
        ], [
            'file.mimes' => 'File harus berupa dokumen (PDF, Word, Excel, PowerPoint) atau gambar (JPG, PNG, GIF, dll)',
            'file.max' => 'Ukuran file maksimal 50MB',
        ]);

        try {
            /** @var \App\Models\MasterPegawai $user */
            $user = $request->user();

            // Check if folder exists and user has access
            $folder = TdFolder::findOrFail($request->folder_id);

            // Get uploaded file
            $uploadedFile = $request->file('file');

            // Get file info BEFORE moving (important!)
            $originalName = $uploadedFile->getClientOriginalName();
            $fileSize = $uploadedFile->getSize();
            $mimeType = $uploadedFile->getMimeType();
            $extension = $uploadedFile->getClientOriginalExtension();

            // Generate unique filename
            $filename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $extension;

            // Define storage path
            $storagePath = "terminal-data/{$folder->bidang_id}/{$folder->id}";

            // Store file directly
            $path = $uploadedFile->storeAs($storagePath, $filename);

            // Create file record
            $file = TdFile::create([
                'folder_id' => $folder->id,
                'original_name' => $originalName,
                'name' => $originalName,
                'filename' => $filename,
                'storage_path' => $path,
                'extension' => $extension,
                'mime_type' => $mimeType,
                'size' => $fileSize,
                'size_kb' => round($fileSize / 1024, 2),
                'is_current' => true,
                'version' => 1,
                'uploaded_by' => $user->id,
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
                    'size' => $file->size_kb,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload file: ' . $e->getMessage()
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

            // Check if file exists in storage
            if (!Storage::exists($file->path)) {
                abort(404, 'File tidak ditemukan');
            }

            return Storage::download($file->path, $file->name);
        } catch (\Exception $e) {
            abort(500, 'Gagal mendownload file: ' . $e->getMessage());
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

            // Update file name
            $file->name = $request->name;
            $file->save();

            return response()->json([
                'success' => true,
                'message' => 'Nama file berhasil diubah',
                'data' => [
                    'id' => $file->id,
                    'name' => $file->name,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah nama file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete file (soft delete - move to trash)
     */
    public function destroy($fileId): JsonResponse
    {
        try {
            $file = TdFile::findOrFail($fileId);

            // Soft delete (move to trash)
            $file->delete();

            // Update folder stats
            if ($file->folder) {
                $file->folder->updateStats();
            }

            return response()->json([
                'success' => true,
                'message' => 'File berhasil dipindahkan ke sampah'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memindahkan file ke sampah: ' . $e->getMessage()
            ], 500);
        }
    }
}

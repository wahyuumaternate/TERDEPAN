<?php

namespace Modules\Dokumen\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Dokumen\Models\Dokumen;
use Modules\Dokumen\Models\Folder;
use Modules\Dokumen\Models\Metadata;
use Modules\Dokumen\Models\NomorCounter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\NomorDokumenService;

class DokumenController extends Controller
{
    protected $nomorService;

    /**
     * Constructor - Inject NomorDokumenService
     */
    public function __construct(NomorDokumenService $nomorService)
    {
        $this->nomorService = $nomorService;
    }

    /**
     * Log document activity
     *
     * @param int $dokumenId
     * @param string $action
     * @return void
     */
    private function logActivity($dokumenId, $action)
    {
        try {
            DB::table('doc_log')->insert([
                'dokumen_id' => $dokumenId,
                'user_id' => Auth::user()->id,
                'action' => $action,
                'ip_address' => request()->ip(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info('Activity logged', [
                'dokumen_id' => $dokumenId,
                'user_id' => Auth::user()->id,
                'action' => $action,
                'ip' => request()->ip()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log activity', [
                'dokumen_id' => $dokumenId,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Dokumen::with(['folder', 'jenis', 'uploader', 'files', 'metadata'])
                    ->orderBy('created_at', 'desc');

                if ($request->filled('kategori')) {
                    $query->whereHas('jenis', function ($q) use ($request) {
                        $q->where('kategori_id', $request->kategori);
                    });
                }

                if ($request->filled('jenis')) {
                    $query->where('jenis_id', $request->jenis);
                }

                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }

                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('judul', 'like', "%{$search}%")
                            ->orWhere('nomor', 'like', "%{$search}%")
                            ->orWhere('nomor_surat', 'like', "%{$search}%")
                            ->orWhere('deskripsi', 'like', "%{$search}%")
                            ->orWhereHas('metadata', function ($mq) use ($search) {
                                $mq->where('value', 'like', "%{$search}%");
                            });
                    });
                }

                $dokumen = $query->get();

                Log::info('Dokumen loaded for index', ['count' => $dokumen->count()]);

                return response()->json($dokumen);
            } catch (\Exception $e) {
                Log::error('Error loading dokumen: ' . $e->getMessage());
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        return view('dokumen::index');
    }

    /**
     * Get folders for dropdown
     */
    public function getFolders()
    {
        try {
            $folders = Folder::select('id', 'nama', 'bidang_id', 'parent_id', 'level', 'path')
                ->orderBy('nama', 'asc')
                ->get();

            return response()->json($folders);
        } catch (\Exception $e) {
            Log::error('Error loading folders: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get bidang list for dropdown
     */
    public function getBidang(Request $request)
    {
        try {
            $bidang = DB::table('master_bidang')
                ->select('id', 'nama', 'kode', 'deskripsi')
                ->orderBy('nama', 'asc')
                ->get();

            return response()->json($bidang);
        } catch (\Exception $e) {
            Log::error('Error fetching bidang: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data bidang',
                'data' => []
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('=== START Store Dokumen ===', [
            'request_data' => $request->except(['file', '_token'])
        ]);

        DB::beginTransaction();
        try {
            // Validasi minimal - hanya file dan folder yang wajib
            $request->validate([
                'folder_id' => 'required|exists:doc_folder,id',
                'file' => 'required|file|max:51200', // 50MB
            ]);

            Log::info('Validation passed');

            // Get folder info
            $folder = \Modules\Dokumen\Models\Folder::findOrFail($request->folder_id);

            // Get file
            $uploadedFile = $request->file('file');
            $originalName = $uploadedFile->getClientOriginalName();
            $extension = $uploadedFile->getClientOriginalExtension();

            // Auto-generate judul dari nama file (tanpa extension)
            $judul = $request->judul ?? pathinfo($originalName, PATHINFO_FILENAME);

            // Create dokumen record
            $dokumen = Dokumen::create([
                'folder_id' => $folder->id,
                'judul' => $judul,
                'nomor' => $request->nomor,
                'tanggal_dokumen' => $request->tanggal_dokumen ?? now(),
                'nomor_surat' => $request->nomor_surat,
                'deskripsi' => $request->deskripsi,
                'status' => $request->status ?? 'Final',
                'uploaded_by' => Auth::user()->id,
                'version' => 1,
            ]);

            Log::info('Dokumen created', ['dokumen_id' => $dokumen->id]);

            // Store file
            $timestamp = now()->format('YmdHis');
            $fileName = $timestamp . '_' . str_replace(' ', '_', $originalName);

            $storedPath = Storage::disk('public')->putFileAs(
                'dokumen/' . $folder->path,
                $uploadedFile,
                $fileName
            );

            Log::info('File stored', ['path' => $storedPath]);

            $fileHash = hash_file('sha256', $uploadedFile->getRealPath());

            $dokumen->files()->create([
                'nama_file' => $originalName,
                'file_path' => $storedPath,
                'size_kb' => round($uploadedFile->getSize() / 1024),
                'extension' => $extension,
                'hash' => $fileHash,
                'version' => 1,
                'is_current' => true,
                'uploaded_by' => Auth::user()->id,
            ]);

            Log::info('File record created');

            // Update folder total_files
            $folder->increment('total_files');

            // Simpan metadata jika ada
            if (!empty($request->metadata)) {
                Log::info('Saving metadata', ['count' => count($request->metadata)]);

                foreach ($request->metadata as $meta) {
                    if (isset($meta['key']) && isset($meta['value']) && !empty($meta['key']) && !empty($meta['value'])) {
                        $dokumen->metadata()->create([
                            'key' => $meta['key'],
                            'value' => $meta['value'],
                        ]);
                    }
                }

                Log::info('Metadata saved');
            }

            // Log activity for upload
            $this->logActivity($dokumen->id, 'Upload');

            DB::commit();

            Log::info('=== END Store Dokumen SUCCESS ===', [
                'dokumen_id' => $dokumen->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'data' => $dokumen->load(['folder', 'uploader', 'files', 'metadata']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            Log::error('Validation error', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('=== ERROR Store Dokumen ===', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal upload dokumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $dokumen = Dokumen::with(['folder', 'jenis', 'files', 'metadata'])->findOrFail($id);
            $dokumen->tanggal_dokumen = $dokumen->tanggal_dokumen->format('Y-m-d');

            // Log the Edit View action
            $this->logActivity($id, 'EditView');

            Log::info('Dokumen loaded for edit', ['dokumen_id' => $id]);

            return response()->json($dokumen);
        } catch (\Exception $e) {
            Log::error('Error editing dokumen: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        Log::info('=== START Update Dokumen ===', [
            'dokumen_id' => $id,
            'request_data' => $request->except(['file', '_token'])
        ]);

        DB::beginTransaction();
        try {
            $dokumen = Dokumen::findOrFail($id);

            $validated = $request->validate([
                'folder_id' => 'required|exists:doc_folder,id',
                'judul' => 'required|string|max:255',
                'tanggal_dokumen' => 'required|date',
                'nomor_surat' => 'nullable|string|max:255',
                'deskripsi' => 'nullable|string',
                'status' => 'required|in:Draft,Final,Archived',
                'file' => 'nullable|file|max:51200', // 50MB
                'metadata' => 'nullable|array',
                'metadata.*.id' => 'nullable|exists:doc_metadata,id',
                'metadata.*.key' => 'required|string|max:100',
                'metadata.*.value' => 'required|string',
                'metadata_delete' => 'nullable|string',
            ]);

            Log::info('Validation passed');

            $dokumen->update([
                'folder_id' => $validated['folder_id'],
                'judul' => $validated['judul'],
                'tanggal_dokumen' => $validated['tanggal_dokumen'],
                'nomor_surat' => $validated['nomor_surat'] ?? null,
                'deskripsi' => $validated['deskripsi'] ?? null,
                'status' => $validated['status'],
            ]);

            Log::info('Dokumen updated');

            // Handle file baru
            if ($request->hasFile('file')) {
                Log::info('Processing new file upload');

                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();

                $folder = $dokumen->folder;
                $timestamp = now()->format('YmdHis');
                $fileName = $timestamp . '_' . str_replace(' ', '_', $originalName);

                $storedPath = Storage::disk('public')->putFileAs(
                    'dokumen/' . $folder->path,
                    $file,
                    $fileName
                );

                $fileHash = hash_file('sha256', $file->getRealPath());

                // Set all files to not current
                $dokumen->files()->update(['is_current' => false]);

                // Increment version
                $newVersion = $dokumen->version + 1;
                $dokumen->update(['version' => $newVersion]);

                // Create new file version
                $dokumen->files()->create([
                    'nama_file' => $originalName,
                    'file_path' => $storedPath,
                    'size_kb' => round($file->getSize() / 1024),
                    'extension' => $extension,
                    'hash' => $fileHash,
                    'version' => $newVersion,
                    'is_current' => true,
                    'uploaded_by' => Auth::user()->id,
                    'keterangan' => 'Updated file',
                ]);

                Log::info('New file version created', ['version' => $newVersion]);

                // Log file update activity
                $this->logActivity($id, 'FileUpdate');
            }

            // Proses metadata
            if (!empty($request->metadata)) {
                Log::info('Processing metadata', ['count' => count($request->metadata)]);

                foreach ($request->metadata as $meta) {
                    if (isset($meta['key']) && isset($meta['value']) && !empty($meta['key']) && !empty($meta['value'])) {
                        if (!empty($meta['id'])) {
                            // Update existing
                            $dokumen->metadata()->where('id', $meta['id'])->update([
                                'key' => $meta['key'],
                                'value' => $meta['value']
                            ]);
                        } else {
                            // Create new
                            $dokumen->metadata()->create([
                                'key' => $meta['key'],
                                'value' => $meta['value'],
                            ]);
                        }
                    }
                }
            }

            // Delete metadata if specified
            if (!empty($request->metadata_delete)) {
                $deleteIds = explode(',', $request->metadata_delete);
                $deleteIds = array_filter($deleteIds);

                if (!empty($deleteIds)) {
                    Log::info('Deleting metadata', ['ids' => $deleteIds]);
                    $dokumen->metadata()->whereIn('id', $deleteIds)->delete();
                }
            }

            // Log the Edit action
            $this->logActivity($id, 'Edit');

            DB::commit();

            Log::info('=== END Update Dokumen SUCCESS ===', ['dokumen_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupdate',
                'data' => $dokumen->load(['folder', 'jenis', 'uploader', 'files', 'metadata'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            Log::error('Validation error', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('=== ERROR Update Dokumen ===', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal update dokumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $dokumen = Dokumen::with(['folder', 'jenis', 'uploader', 'files', 'metadata'])->findOrFail($id);
            $dokumen->increment('views');

            // Log the View action
            $this->logActivity($id, 'View');

            Log::info('Dokumen viewed', ['dokumen_id' => $id, 'views' => $dokumen->views]);

            return response()->json($dokumen);
        } catch (\Exception $e) {
            Log::error('Error showing dokumen: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Download the specified resource.
     */
    public function download($id)
    {
        try {
            $dokumen = Dokumen::with('files')->findOrFail($id);
            $file = $dokumen->files()->where('is_current', true)->latest()->first();

            if (!$file) {
                abort(404, 'File tidak ditemukan');
            }

            $dokumen->increment('downloads');

            // Log the Download action
            $this->logActivity($id, 'Download');

            $filePath = storage_path('app/public/' . $file->file_path);

            if (!file_exists($filePath)) {
                Log::error('File not found in storage', ['path' => $filePath]);
                abort(404, 'File tidak ditemukan di storage');
            }

            Log::info('File downloaded', [
                'dokumen_id' => $id,
                'file_name' => $file->nama_file,
                'downloads' => $dokumen->downloads
            ]);

            return response()->download($filePath, $file->nama_file);
        } catch (\Exception $e) {
            Log::error('Error downloading dokumen: ' . $e->getMessage());
            abort(500, 'Gagal mendownload file');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Log::info('=== START Delete Dokumen ===', ['dokumen_id' => $id]);

        DB::beginTransaction();
        try {
            $dokumen = Dokumen::findOrFail($id);

            // Log the Delete action before actually deleting
            $this->logActivity($id, 'Delete');

            // Delete associated files from storage
            foreach ($dokumen->files as $file) {
                if (Storage::disk('public')->exists($file->file_path)) {
                    Storage::disk('public')->delete($file->file_path);
                    Log::info('File deleted from storage', ['path' => $file->file_path]);
                }
            }

            // Decrement folder total_files
            if ($dokumen->folder) {
                $dokumen->folder->decrement('total_files');
            }

            $dokumen->delete();

            DB::commit();

            Log::info('=== END Delete Dokumen SUCCESS ===', ['dokumen_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('=== ERROR Delete Dokumen ===', [
                'message' => $e->getMessage(),
                'dokumen_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus dokumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent activity logs for dashboard
     * 
     * @param int $limit Number of logs to return
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecentActivityLogs($limit = 5)
    {
        try {
            $logs = DB::table('doc_log')
                ->join('doc_dokumen', 'doc_log.dokumen_id', '=', 'doc_dokumen.id')
                ->join('master_pegawai', 'doc_log.user_id', '=', 'master_pegawai.id')
                ->select(
                    'doc_log.id',
                    'doc_log.dokumen_id',
                    'doc_log.user_id',
                    'doc_log.action',
                    'doc_log.created_at',
                    'doc_dokumen.judul as dokumen_judul',
                    'doc_dokumen.nomor as dokumen_nomor',
                    'master_pegawai.nama as user_nama'
                )
                ->orderBy('doc_log.created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting recent activity logs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat aktivitas terbaru',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ===== HELPER METHODS =====

}

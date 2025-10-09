<?php

namespace Modules\Dokumen\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Dokumen\Models\Dokumen;
use Modules\Dokumen\Models\Folder;
use Modules\Dokumen\Models\JenisDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class DokumenController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Dokumen::with(['folder', 'jenis', 'uploader', 'files'])
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
                            ->orWhere('deskripsi', 'like', "%{$search}%");
                    });
                }

                $dokumen = $query->get();
                return response()->json($dokumen);
            } catch (\Exception $e) {
                Log::error('Error loading dokumen: ' . $e->getMessage());
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        return view('dokumen::index');
    }

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

    public function getJenis()
    {
        try {
            // TAMBAHKAN allowed_ext, max_size_mb, folder_pattern
            $jenis = JenisDokumen::select('id', 'nama', 'kode', 'kategori_id', 'allowed_ext', 'max_size_mb', 'folder_pattern')
                ->orderBy('nama', 'asc')
                ->get();

            return response()->json($jenis);
        } catch (\Exception $e) {
            Log::error('Error loading jenis: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Get jenis dokumen untuk validasi dinamis
            $jenis = JenisDokumen::findOrFail($request->jenis_id);

            // Build dynamic validation rules
            $allowedExtensions = $jenis->allowed_ext ?: 'pdf,doc,docx,xls,xlsx';
            $maxSize = ($jenis->max_size_mb ?: 10) * 1024; // Convert MB to KB

            $validated = $request->validate([
                'folder_id' => 'required|exists:doc_folder,id',
                'jenis_id' => 'required|exists:doc_jenis,id',
                'judul' => 'required|string|max:255',
                'tanggal_dokumen' => 'required|date',
                'nomor_surat' => 'nullable|string|max:255',
                'deskripsi' => 'nullable|string',
                'status' => 'required|in:Draft,Final,Archived',
                'file' => "required|file|mimes:{$allowedExtensions}|max:{$maxSize}",
            ]);

            // Auto-create folder berdasarkan pattern
            $folder = $this->getOrCreateFolderFromPattern($jenis, $request->folder_id);

            // Generate nomor dokumen jika perlu
            $validated['nomor'] = $jenis->perlu_nomor
                ? $this->generateNomorDokumen($validated['jenis_id'])
                : null;

            $validated['uploaded_by'] = auth()->user()->id;
            $validated['version'] = 1;
            $validated['folder_id'] = $folder->id; // Use generated folder

            $dokumen = Dokumen::create($validated);

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');

                // Generate path sesuai pattern
                $filePath = $this->generateFilePath($jenis, $file);

                // Store file
                $storedPath = Storage::disk('public')->putFileAs(
                    dirname($filePath),
                    $file,
                    basename($filePath)
                );

                // Hitung hash file
                $fileHash = hash_file('sha256', $file->getRealPath());

                // Simpan info file
                $dokumen->files()->create([
                    'nama_file' => $file->getClientOriginalName(),
                    'file_path' => $storedPath,
                    'size_kb' => round($file->getSize() / 1024),
                    'extension' => $file->getClientOriginalExtension(),
                    'hash' => $fileHash,
                    'version' => 1,
                    'is_current' => true,
                    'uploaded_by' => auth()->user()->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'data' => $dokumen->load(['folder', 'jenis', 'uploader', 'files'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error uploading dokumen: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload dokumen: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $dokumen = Dokumen::with(['folder', 'jenis', 'files'])->findOrFail($id);

            // Format tanggal untuk input
            $dokumen->tanggal_dokumen = $dokumen->tanggal_dokumen->format('Y-m-d');

            return response()->json($dokumen);
        } catch (\Exception $e) {
            Log::error('Error editing dokumen: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $dokumen = Dokumen::findOrFail($id);
            $jenis = JenisDokumen::findOrFail($request->jenis_id);

            // Dynamic validation
            $allowedExtensions = $jenis->allowed_ext ?: 'pdf,doc,docx,xls,xlsx';
            $maxSize = ($jenis->max_size_mb ?: 10) * 1024;

            $validated = $request->validate([
                'folder_id' => 'required|exists:doc_folder,id',
                'jenis_id' => 'required|exists:doc_jenis,id',
                'judul' => 'required|string|max:255',
                'tanggal_dokumen' => 'required|date',
                'nomor_surat' => 'nullable|string|max:255',
                'deskripsi' => 'nullable|string',
                'status' => 'required|in:Draft,Final,Archived',
                'file' => "nullable|file|mimes:{$allowedExtensions}|max:{$maxSize}",
            ]);

            // Update data dokumen
            $dokumen->update([
                'folder_id' => $validated['folder_id'],
                'jenis_id' => $validated['jenis_id'],
                'judul' => $validated['judul'],
                'tanggal_dokumen' => $validated['tanggal_dokumen'],
                'nomor_surat' => $validated['nomor_surat'] ?? null,
                'deskripsi' => $validated['deskripsi'] ?? null,
                'status' => $validated['status'],
            ]);

            // Handle file baru
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filePath = $this->generateFilePath($jenis, $file);

                $storedPath = Storage::disk('public')->putFileAs(
                    dirname($filePath),
                    $file,
                    basename($filePath)
                );

                $fileHash = hash_file('sha256', $file->getRealPath());

                // Set all files to not current
                $dokumen->files()->update(['is_current' => false]);

                // Increment version
                $newVersion = $dokumen->version + 1;
                $dokumen->update(['version' => $newVersion]);

                // Create new file version
                $dokumen->files()->create([
                    'nama_file' => $file->getClientOriginalName(),
                    'file_path' => $storedPath,
                    'size_kb' => round($file->getSize() / 1024),
                    'extension' => $file->getClientOriginalExtension(),
                    'hash' => $fileHash,
                    'version' => $newVersion,
                    'is_current' => true,
                    'uploaded_by' => auth()->user()->id,
                    'keterangan' => 'Updated file',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupdate',
                'data' => $dokumen->load(['folder', 'jenis', 'uploader', 'files'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating dokumen: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal update dokumen: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $dokumen = Dokumen::with(['folder', 'jenis', 'uploader', 'files'])->findOrFail($id);
            $dokumen->increment('views');
            return response()->json($dokumen);
        } catch (\Exception $e) {
            Log::error('Error showing dokumen: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function download($id)
    {
        try {
            $dokumen = Dokumen::with('files')->findOrFail($id);
            $file = $dokumen->files()->where('is_current', true)->latest()->first();

            if (!$file) {
                abort(404, 'File tidak ditemukan');
            }

            $dokumen->increment('downloads');

            $filePath = storage_path('app/public/' . $file->file_path);

            if (!file_exists($filePath)) {
                abort(404, 'File tidak ditemukan di storage');
            }

            return response()->download($filePath, $file->nama_file);
        } catch (\Exception $e) {
            Log::error('Error downloading dokumen: ' . $e->getMessage());
            abort(500, 'Gagal mendownload file');
        }
    }

    public function destroy($id)
    {
        try {
            $dokumen = Dokumen::findOrFail($id);

            foreach ($dokumen->files as $file) {
                Storage::disk('public')->delete($file->file_path);
            }

            $dokumen->delete();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting dokumen: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus dokumen: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===== HELPER METHODS =====

    /**
     * Generate file path sesuai pattern dari jenis dokumen
     * Pattern: /{bidang}/{jenis}/{year}/{month}/
     */
    private function generateFilePath($jenis, $file)
    {
        $pattern = $jenis->folder_pattern ?: '/dokumen/{year}/{month}/';

        $user = auth()->user();
        $bidangKode = $user->bidang->kode ?? 'UMUM';
        $jenisKode = $jenis->kode ?? 'DOC';

        $path = str_replace(
            ['{bidang}', '{jenis}', '{year}', '{month}'],
            [strtolower($bidangKode), strtolower($jenisKode), date('Y'), date('m')],
            $pattern
        );

        // Remove leading slash and ensure trailing slash
        $path = trim($path, '/') . '/';

        // Generate unique filename
        $filename = time() . '_' . $file->getClientOriginalName();

        return $path . $filename;
    }

    /**
     * Get or create folder based on pattern
     */
    private function getOrCreateFolderFromPattern($jenis, $baseFolderId)
    {
        $pattern = $jenis->folder_pattern ?: '/dokumen/{year}/{month}/';

        $user = auth()->user();
        $bidangKode = $user->bidang->kode ?? 'UMUM';
        $jenisKode = $jenis->kode ?? 'DOC';

        $path = str_replace(
            ['{bidang}', '{jenis}', '{year}', '{month}'],
            [strtolower($bidangKode), strtolower($jenisKode), date('Y'), date('m')],
            $pattern
        );

        // Cari atau buat folder
        $folder = Folder::where('path', $path)->first();

        if (!$folder) {
            $folder = Folder::create([
                'parent_id' => $baseFolderId,
                'bidang_id' => $user->bidang_id ?? null,
                'nama' => date('Y-m'),
                'path' => $path,
                'level' => 2,
                'is_auto' => true,
                'total_files' => 0,
                'created_by' => $user->id,
            ]);
        }

        return $folder;
    }

    /**
     * Generate nomor dokumen
     */
    private function generateNomorDokumen($jenisId)
    {
        try {
            $jenis = JenisDokumen::find($jenisId);
            if (!$jenis || !$jenis->perlu_nomor) {
                return null;
            }

            $year = date('Y');
            $lastDokumen = Dokumen::where('jenis_id', $jenisId)
                ->whereYear('created_at', $year)
                ->latest('id')
                ->first();

            $sequence = $lastDokumen ? (int)substr($lastDokumen->nomor, -4) + 1 : 1;

            $user = auth()->user();
            $bidangKode = $user->bidang->kode ?? 'UMUM';

            // Gunakan nomor_format dari jenis
            $format = $jenis->nomor_format ?: 'SM/BAPPEDA/{bidang}/{year}/{seq}';

            return str_replace(
                ['{bidang}', '{year}', '{seq}'],
                [strtoupper($bidangKode), $year, sprintf('%04d', $sequence)],
                $format
            );
        } catch (\Exception $e) {
            Log::error('Error generating nomor dokumen: ' . $e->getMessage());
            return 'DOC/' . date('Y') . '/' . rand(1000, 9999);
        }
    }
}

<?php

namespace Modules\Dokumen\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Dokumen\Models\File as DokumenFile;
use Modules\Dokumen\Models\JenisDokumen;

class FileController extends Controller
{
    public function index()
    {
        $files = DokumenFile::with('dokumen', 'uploader')->get();
        return response()->json($files);
    }

    public function create()
    {
        return view('dokumen::file.create');
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
            ], [
                'file.mimes' => 'File harus berformat: ' . strtoupper(str_replace(',', ', ', $allowedExtensions)),
                'file.max' => 'Ukuran file maksimal ' . ($jenis->max_size_mb ?: 10) . 'MB',
            ]);

            // Auto-create folder berdasarkan pattern
            $folder = $this->getOrCreateFolderFromPattern($jenis, $request->folder_id);

            // Generate nomor dokumen jika perlu
            $validated['nomor'] = $jenis->perlu_nomor
                ? $this->generateNomorDokumen($validated['jenis_id'])
                : null;

            $validated['uploaded_by'] = auth()->user()->id;
            $validated['version'] = 1;
            $validated['folder_id'] = $folder->id;

            $dokumen = DokumenFile::create($validated);

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

    public function show($id)
    {
        $file = DokumenFile::with('dokumen', 'uploader')->findOrFail($id);
        return response()->json($file);
    }

    public function edit($id)
    {
        $file = DokumenFile::findOrFail($id);
        return view('dokumen::file.edit', compact('file'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'keterangan' => 'nullable|string',
            'is_current' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = DokumenFile::findOrFail($id);
        $file->update($request->all());
        return response()->json($file);
    }

    public function destroy($id)
    {
        $file = DokumenFile::findOrFail($id);

        // Delete physical file
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();
        return response()->json(['message' => 'File berhasil dihapus']);
    }

    public function getVersions($dokumenId)
    {
        $files = DokumenFile::where('dokumen_id', $dokumenId)
            ->orderBy('version', 'desc')
            ->get();
        return response()->json($files);
    }
}

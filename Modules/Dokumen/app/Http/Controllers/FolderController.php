<?php

namespace Modules\Dokumen\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Dokumen\Models\Dokumen;
use Modules\Dokumen\Models\File;
use Modules\Dokumen\Models\Folder;

class FolderController extends Controller
{
    public function index(Request $request)
    {
        // Jika request AJAX, return JSON
        if ($request->wantsJson() || $request->ajax()) {
            $folders = Folder::with(['parent', 'bidang', 'creator'])
                ->orderBy('level')
                ->orderBy('nama')
                ->get();

            // Debug: Log bidang data
            // Log::info('Folders with bidang:', $folders->toArray());

            return response()->json($folders);
        }

        // Jika bukan AJAX, return view
        $folders = Folder::with(['parent', 'bidang', 'creator'])
            ->whereNull('parent_id')
            ->orderBy('nama')
            ->get();

        return view('dokumen::folder.index', compact('folders'));
    }

    /**
     * Display documents for a specific folder
     */
    public function getFolderDokumen($id)
    {
        try {
            $folder = Folder::with('bidang')->findOrFail($id);
            $dokumen = Dokumen::with(['jenis', 'uploader', 'files'])
                ->where('folder_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            return view('dokumen::folder.dokumen', [
                'folder' => $folder,
                'dokumen' => $dokumen
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading folder documents: ' . $e->getMessage());
            return redirect()->route('dokumen.folder.index')->with('error', 'Gagal memuat dokumen folder');
        }
    }
    public function create()
    {
        return view('dokumen::folder.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'nullable|exists:doc_folder,id',
            'bidang_id' => 'nullable|exists:master_bidang,id',
            'nama' => 'required|string|max:100',
            'is_auto' => 'nullable|boolean'
        ], [
            'parent_id.exists' => 'Parent folder tidak ditemukan',
            'bidang_id.exists' => 'Bidang tidak ditemukan',
            'nama.required' => 'Nama folder wajib diisi',
            'nama.max' => 'Nama folder maksimal 100 karakter'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Auto-generate path from nama
            $slugName = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->nama)));

            // Remove multiple dashes and trim
            $slugName = preg_replace('/-+/', '-', $slugName);
            $slugName = trim($slugName, '-');

            // Get parent folder if exists
            $parent = null;
            if ($request->parent_id) {
                $parent = Folder::find($request->parent_id);
                if (!$parent) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Parent folder tidak ditemukan'
                    ], 404);
                }
            }

            // Build path
            if ($parent) {
                $basePath = $parent->path . '/' . $slugName;
                $bidangId = $parent->bidang_id;
                $level = $parent->level + 1;
            } else {
                $basePath = '/' . $slugName;
                $bidangId = $request->bidang_id;
                $level = 0;
            }

            // Check if path already exists, if so add number suffix
            $finalPath = $basePath;
            $counter = 1;
            while (Folder::where('path', $finalPath)->exists()) {
                $finalPath = $basePath . '-' . $counter;
                $counter++;
            }

            // Build data array - explicitly define each field
            $folder = Folder::create([
                'parent_id' => $request->parent_id,
                'bidang_id' => $bidangId,
                'nama' => $request->nama,
                'path' => $finalPath,
                'level' => $level,
                'is_auto' => $request->has('is_auto') ? (bool) $request->is_auto : false,
                'total_files' => 0,
                'created_by' => Auth::id()
            ]);

            DB::commit();

            // Load relationships for response
            $folder->load(['parent', 'bidang', 'creator']);

            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil ditambahkan',
                'data' => $folder
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating folder', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan folder: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $folder = Folder::with(['parent', 'children', 'bidang', 'dokumen', 'creator'])
                ->findOrFail($id);

            return response()->json($folder);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Folder tidak ditemukan'
            ], 404);
        }
    }

    public function edit($id)
    {
        $folder = Folder::findOrFail($id);
        return view('dokumen::folder.edit', compact('folder'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'nullable|exists:doc_folder,id',
            'bidang_id' => 'nullable|exists:master_bidang,id',
            'nama' => 'required|string|max:100',
            'path' => 'required|string|unique:doc_folder,path,' . $id,
            'level' => 'nullable|integer|min:0',
            'is_auto' => 'nullable|boolean'
        ], [
            'parent_id.exists' => 'Parent folder tidak ditemukan',
            'bidang_id.exists' => 'Bidang tidak ditemukan',
            'nama.required' => 'Nama folder wajib diisi',
            'nama.max' => 'Nama folder maksimal 100 karakter',
            'path.required' => 'Path folder wajib diisi',
            'path.unique' => 'Path folder sudah digunakan',
            'level.min' => 'Level tidak boleh negatif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $folder = Folder::findOrFail($id);

            // Prevent circular reference
            if ($request->parent_id) {
                if ($request->parent_id == $id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Folder tidak bisa menjadi parent dari dirinya sendiri'
                    ], 422);
                }

                // Check if parent_id is a descendant of current folder
                if ($this->isDescendant($id, $request->parent_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak bisa memindahkan folder ke dalam subfolder-nya sendiri'
                    ], 422);
                }
            }

            $data = $request->all();
            $data['is_auto'] = $request->has('is_auto') ? (bool) $request->is_auto : false;

            // Auto-calculate level if parent changed
            if ($request->has('parent_id') && $request->parent_id != $folder->parent_id) {
                if ($request->parent_id) {
                    $parent = Folder::find($request->parent_id);
                    $data['level'] = $parent ? $parent->level + 1 : 0;
                } else {
                    $data['level'] = 0;
                }
            }

            $folder->update($data);

            DB::commit();

            // Load relationships for response
            $folder->load(['parent', 'bidang', 'creator']);

            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil diupdate',
                'data' => $folder
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate folder: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $folder = Folder::findOrFail($id);

            // Check if folder has children
            $childrenCount = Folder::where('parent_id', $id)->count();
            if ($childrenCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus folder yang memiliki subfolder'
                ], 422);
            }

            // Check if folder has documents
            if ($folder->total_files > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus folder yang berisi dokumen'
                ], 422);
            }

            $folder->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus folder: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getChildren($id)
    {
        try {
            $children = Folder::with(['bidang', 'creator'])
                ->where('parent_id', $id)
                ->orderBy('nama')
                ->get();

            return response()->json($children);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data subfolder'
            ], 500);
        }
    }

    /**
     * Check if target folder is a descendant of source folder
     */
    private function isDescendant($sourceId, $targetId)
    {
        $current = Folder::find($targetId);

        while ($current && $current->parent_id) {
            if ($current->parent_id == $sourceId) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }

    /**
     * Get folder tree structure
     */
    public function getTree(Request $request)
    {
        $folders = Folder::with(['bidang', 'creator'])
            ->whereNull('parent_id')
            ->orderBy('nama')
            ->get();

        $tree = $this->buildTree($folders);

        return response()->json($tree);
    }

    /**
     * Build tree structure recursively
     */
    private function buildTree($folders)
    {
        return $folders->map(function ($folder) {
            return [
                'id' => $folder->id,
                'nama' => $folder->nama,
                'path' => $folder->path,
                'level' => $folder->level,
                'is_auto' => $folder->is_auto,
                'total_files' => $folder->total_files,
                'bidang' => $folder->bidang,
                'children' => $this->buildTree($folder->children)
            ];
        });
    }

    /**
     * Get files in a folder
     */
    public function getFiles($id)
    {
        try {
            $folder = Folder::findOrFail($id);

            // Fetch files from the database
            $files = File::where('folder_id', $id)
                ->with(['uploader', 'updater', 'jenis'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'folder' => $folder,
                'files' => $files
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting folder files: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}

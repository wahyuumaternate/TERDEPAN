<?php

namespace Modules\Dokumen\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Modules\Dokumen\Model\Folder as DokumenFolder;

class FolderController extends Controller
{
    public function index()
    {
        $folders = DokumenFolder::with('parent', 'bidang', 'creator')
            ->whereNull('parent_id')
            ->get();
        return response()->json($folders);
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
            'path' => 'required|string|unique:doc_folder,path',
            'level' => 'nullable|integer',
            'is_auto' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['created_by'] = Auth::id(); // atau sesuaikan dengan sistem auth Anda

        $folder = DokumenFolder::create($data);
        return response()->json($folder, 201);
    }

    public function show($id)
    {
        $folder = DokumenFolder::with('parent', 'children', 'bidang', 'dokumen', 'creator')
            ->findOrFail($id);
        return response()->json($folder);
    }

    public function edit($id)
    {
        $folder = DokumenFolder::findOrFail($id);
        return view('dokumen::folder.edit', compact('folder'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'nullable|exists:doc_folder,id',
            'bidang_id' => 'nullable|exists:master_bidang,id',
            'nama' => 'required|string|max:100',
            'path' => 'required|string|unique:doc_folder,path,' . $id,
            'level' => 'nullable|integer',
            'is_auto' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $folder = DokumenFolder::findOrFail($id);
        $folder->update($request->all());
        return response()->json($folder);
    }

    public function destroy($id)
    {
        $folder = DokumenFolder::findOrFail($id);
        $folder->delete();
        return response()->json(['message' => 'Folder berhasil dihapus']);
    }

    public function getChildren($id)
    {
        $children = DokumenFolder::where('parent_id', $id)->get();
        return response()->json($children);
    }
}

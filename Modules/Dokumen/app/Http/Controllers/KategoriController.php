<?php

namespace Modules\Dokumen\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Dokumen\Models\Kategori;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $kategoris = Kategori::orderBy('urutan')->get();
            return response()->json($kategoris);
        }

        // Return view untuk non-AJAX request
        return view('dokumen::kategori.index');
    }

    public function create()
    {
        return view('dokumen::kategori.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'icon' => 'nullable|string|max:50',
            'warna' => 'nullable|string|max:7',
            'urutan' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $kategori = Kategori::create($request->all());
        return response()->json($kategori, 201);
    }

    public function show($id)
    {
        $kategori = Kategori::with('jenis')->findOrFail($id);
        return response()->json($kategori);
    }

    public function edit($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('dokumen::kategori.edit', compact('kategori'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'icon' => 'nullable|string|max:50',
            'warna' => 'nullable|string|max:7',
            'urutan' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $kategori = Kategori::findOrFail($id);
        $kategori->update($request->all());
        return response()->json($kategori);
    }

    public function destroy($id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->delete();
        return response()->json(['message' => 'Kategori berhasil dihapus']);
    }
}

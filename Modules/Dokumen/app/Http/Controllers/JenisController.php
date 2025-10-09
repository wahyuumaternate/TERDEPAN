<?php

namespace Modules\Dokumen\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Dokumen\Model\JenisDokumen as DokumenJenis;

class JenisController extends Controller
{
    public function index()
    {
        $jenis = DokumenJenis::with('kategori')->get();
        return response()->json($jenis);
    }

    public function create()
    {
        return view('dokumen::jenis.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kategori_id' => 'required|exists:doc_kategori,id',
            'kode' => 'required|string|max:20|unique:doc_jenis,kode',
            'nama' => 'required|string|max:100',
            'folder_pattern' => 'nullable|string',
            'nomor_format' => 'nullable|string',
            'allowed_ext' => 'nullable|string',
            'max_size_mb' => 'nullable|integer',
            'perlu_nomor' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $jenis = DokumenJenis::create($request->all());
        return response()->json($jenis, 201);
    }

    public function show($id)
    {
        $jenis = DokumenJenis::with('kategori', 'dokumen')->findOrFail($id);
        return response()->json($jenis);
    }

    public function edit($id)
    {
        $jenis = DokumenJenis::findOrFail($id);
        return view('dokumen::jenis.edit', compact('jenis'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'kategori_id' => 'required|exists:doc_kategori,id',
            'kode' => 'required|string|max:20|unique:doc_jenis,kode,' . $id,
            'nama' => 'required|string|max:100',
            'folder_pattern' => 'nullable|string',
            'nomor_format' => 'nullable|string',
            'allowed_ext' => 'nullable|string',
            'max_size_mb' => 'nullable|integer',
            'perlu_nomor' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $jenis = DokumenJenis::findOrFail($id);
        $jenis->update($request->all());
        return response()->json($jenis);
    }

    public function destroy($id)
    {
        $jenis = DokumenJenis::findOrFail($id);
        $jenis->delete();
        return response()->json(['message' => 'Jenis dokumen berhasil dihapus']);
    }
}

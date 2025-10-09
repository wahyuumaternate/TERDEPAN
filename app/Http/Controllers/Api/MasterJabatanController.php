<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterJabatan;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="MasterJabatan",
 *     description="API Master Jabatan"
 * )
 *
 * @OA\Get(
 *     path="/master-jabatan",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterJabatan"},
 *     summary="List semua jabatan",
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Get(
 *     path="/master-jabatan/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterJabatan"},
 *     summary="Detail jabatan",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Post(
 *     path="/master-jabatan",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterJabatan"},
 *     summary="Tambah jabatan",
 *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="application/json")),
 *     @OA\Response(response=201, description="Created")
 * )
 *
 * @OA\Put(
 *     path="/master-jabatan/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterJabatan"},
 *     summary="Update jabatan",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="application/json")),
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Delete(
 *     path="/master-jabatan/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterJabatan"},
 *     summary="Hapus jabatan",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="OK")
 * )
 */
class MasterJabatanController extends Controller
{
    public function index()
    {
        $data = MasterJabatan::with('pegawai')->get();
        return response()->json([
            'status' => true,
            'message' => 'List semua jabatan',
            'data' => $data
        ]);
    }

    public function show($id)
    {
        $jabatan = MasterJabatan::with('pegawai')->findOrFail($id);
        return response()->json([
            'status' => true,
            'message' => 'Detail jabatan',
            'data' => $jabatan
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|unique:master_jabatan,kode',
            'nama' => 'required',
            'level' => 'required|integer',
            'is_struktural' => 'boolean',
            'bebas_nilai_kinerja' => 'boolean',
            'is_active' => 'boolean',
        ]);
        $jabatan = MasterJabatan::create($data);
        return response()->json([
            'status' => true,
            'message' => 'Jabatan berhasil ditambah',
            'data' => $jabatan
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $jabatan = MasterJabatan::findOrFail($id);
        $data = $request->validate([
            'nama' => 'sometimes',
            'level' => 'sometimes|integer',
            'is_struktural' => 'sometimes|boolean',
            'bebas_nilai_kinerja' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);
        $jabatan->update($data);
        return response()->json([
            'status' => true,
            'message' => 'Jabatan berhasil diupdate',
            'data' => $jabatan
        ]);
    }

    public function destroy($id)
    {
        $jabatan = MasterJabatan::findOrFail($id);
        $jabatan->delete();
        return response()->json([
            'status' => true,
            'message' => 'Jabatan berhasil dihapus',
            'data' => null
        ]);
    }
}

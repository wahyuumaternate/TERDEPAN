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
 *     description="Mengambil detail data jabatan beserta relasi pegawai.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID jabatan",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="OK",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Detail jabatan"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="kode", type="string", example="JAB001"),
 *                 @OA\Property(property="nama", type="string", example="Kepala Bidang"),
 *                 @OA\Property(property="level", type="integer", example=2),
 *                 @OA\Property(property="is_struktural", type="boolean", example=true),
 *                 @OA\Property(property="bebas_nilai_kinerja", type="boolean", example=false),
 *                 @OA\Property(property="is_active", type="boolean", example=true),
 *                 @OA\Property(property="pegawai", type="array", @OA\Items(type="object"))
 *             )
 *         )
 *     ),
 *     @OA\Response(response=404, description="Jabatan tidak ditemukan")
 * )
 *
 * @OA\Post(
 *     path="/master-jabatan",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterJabatan"},
 *     summary="Tambah jabatan",
 *     description="Menambah data jabatan baru.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"kode","nama","level"},
 *             @OA\Property(property="kode", type="string", example="JAB001"),
 *             @OA\Property(property="nama", type="string", example="Kepala Bidang"),
 *             @OA\Property(property="level", type="integer", example=2),
 *             @OA\Property(property="is_struktural", type="boolean", example=true),
 *             @OA\Property(property="bebas_nilai_kinerja", type="boolean", example=false),
 *             @OA\Property(property="is_active", type="boolean", example=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Created",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Jabatan berhasil ditambah"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="kode", type="string", example="JAB001"),
 *                 @OA\Property(property="nama", type="string", example="Kepala Bidang"),
 *                 @OA\Property(property="level", type="integer", example=2),
 *                 @OA\Property(property="is_struktural", type="boolean", example=true),
 *                 @OA\Property(property="bebas_nilai_kinerja", type="boolean", example=false),
 *                 @OA\Property(property="is_active", type="boolean", example=true)
 *             )
 *         )
 *     ),
 *     @OA\Response(response=422, description="Validasi gagal")
 * )
 *
 * @OA\Put(
 *     path="/master-jabatan/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterJabatan"},
 *     summary="Update jabatan",
 *     description="Mengupdate data jabatan berdasarkan ID.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID jabatan",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="nama", type="string", example="Kepala Bidang"),
 *             @OA\Property(property="level", type="integer", example=2),
 *             @OA\Property(property="is_struktural", type="boolean", example=true),
 *             @OA\Property(property="bebas_nilai_kinerja", type="boolean", example=false),
 *             @OA\Property(property="is_active", type="boolean", example=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="OK",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Jabatan berhasil diupdate"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="kode", type="string", example="JAB001"),
 *                 @OA\Property(property="nama", type="string", example="Kepala Bidang"),
 *                 @OA\Property(property="level", type="integer", example=2),
 *                 @OA\Property(property="is_struktural", type="boolean", example=true),
 *                 @OA\Property(property="bebas_nilai_kinerja", type="boolean", example=false),
 *                 @OA\Property(property="is_active", type="boolean", example=true)
 *             )
 *         )
 *     ),
 *     @OA\Response(response=404, description="Jabatan tidak ditemukan"),
 *     @OA\Response(response=422, description="Validasi gagal")
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
        try {
            $data = MasterJabatan::with('pegawai')->get();
            return response()->json([
                'status' => true,
                'message' => 'List semua jabatan',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $jabatan = MasterJabatan::with('pegawai')->findOrFail($id);
            return response()->json([
                'status' => true,
                'message' => 'Detail jabatan',
                'data' => $jabatan
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Jabatan tidak ditemukan',
                'data' => null
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Jabatan tidak ditemukan',
                'data' => null
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $jabatan = MasterJabatan::findOrFail($id);
            $jabatan->delete();
            return response()->json([
                'status' => true,
                'message' => 'Jabatan berhasil dihapus',
                'data' => null
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Jabatan tidak ditemukan',
                'data' => null
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}

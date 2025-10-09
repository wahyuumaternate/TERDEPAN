<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterBidang;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="MasterBidang",
 *     description="API Master Bidang"
 * )
 *
 * @OA\Get(
 *     path="/master-bidang",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterBidang"},
 *     summary="List semua bidang",
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Get(
 *     path="/master-bidang/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterBidang"},
 *     summary="Detail bidang",
 *     description="Mengambil detail data bidang beserta relasi pegawai.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID bidang",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="OK",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Detail bidang"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="kode", type="string", example="BID001"),
 *                 @OA\Property(property="nama", type="string", example="Bidang Perencanaan Ekonomi"),
 *                 @OA\Property(property="deskripsi", type="string", example="Deskripsi bidang"),
 *                 @OA\Property(property="warna", type="string", example="#FF0000"),
 *                 @OA\Property(property="is_active", type="boolean", example=true),
 *                 @OA\Property(property="pegawai", type="array", @OA\Items(type="object"))
 *             )
 *         )
 *     ),
 *     @OA\Response(response=404, description="Bidang tidak ditemukan")
 * )
 *
 * @OA\Post(
 *     path="/master-bidang",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterBidang"},
 *     summary="Tambah bidang",
 *     description="Menambah data bidang baru.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"kode","nama"},
 *             @OA\Property(property="kode", type="string", example="BID001"),
 *             @OA\Property(property="nama", type="string", example="Bidang Perencanaan Ekonomi"),
 *             @OA\Property(property="deskripsi", type="string", example="Deskripsi bidang"),
 *             @OA\Property(property="warna", type="string", example="#FF0000"),
 *             @OA\Property(property="is_active", type="boolean", example=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Created",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Bidang berhasil ditambah"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="kode", type="string", example="BID001"),
 *                 @OA\Property(property="nama", type="string", example="Bidang Perencanaan Ekonomi"),
 *                 @OA\Property(property="deskripsi", type="string", example="Deskripsi bidang"),
 *                 @OA\Property(property="warna", type="string", example="#FF0000"),
 *                 @OA\Property(property="is_active", type="boolean", example=true)
 *             )
 *         )
 *     ),
 *     @OA\Response(response=422, description="Validasi gagal")
 * )
 *
 * @OA\Put(
 *     path="/master-bidang/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterBidang"},
 *     summary="Update bidang",
 *     description="Mengupdate data bidang berdasarkan ID.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID bidang",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="nama", type="string", example="Bidang Perencanaan Ekonomi"),
 *             @OA\Property(property="deskripsi", type="string", example="Deskripsi bidang"),
 *             @OA\Property(property="warna", type="string", example="#FF0000"),
 *             @OA\Property(property="is_active", type="boolean", example=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="OK",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Bidang berhasil diupdate"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="kode", type="string", example="BID001"),
 *                 @OA\Property(property="nama", type="string", example="Bidang Perencanaan Ekonomi"),
 *                 @OA\Property(property="deskripsi", type="string", example="Deskripsi bidang"),
 *                 @OA\Property(property="warna", type="string", example="#FF0000"),
 *                 @OA\Property(property="is_active", type="boolean", example=true)
 *             )
 *         )
 *     ),
 *     @OA\Response(response=404, description="Bidang tidak ditemukan"),
 *     @OA\Response(response=422, description="Validasi gagal")
 * )
 *
 * @OA\Delete(
 *     path="/master-bidang/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterBidang"},
 *     summary="Hapus bidang",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="OK")
 * )
 */
class MasterBidangController extends Controller
{
    public function index()
    {
        try {
            $data = MasterBidang::with('pegawai')->get();
            return response()->json([
                'status' => true,
                'message' => 'List semua bidang',
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
            $bidang = MasterBidang::with('pegawai')->findOrFail($id);
            return response()->json([
                'status' => true,
                'message' => 'Detail bidang',
                'data' => $bidang
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Bidang tidak ditemukan',
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
                'kode' => 'required|unique:master_bidang,kode',
                'nama' => 'required',
                'deskripsi' => 'nullable',
                'warna' => 'nullable',
                'is_active' => 'boolean',
            ]);
            $bidang = MasterBidang::create($data);
            return response()->json([
                'status' => true,
                'message' => 'Bidang berhasil ditambah',
                'data' => $bidang
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
            $bidang = MasterBidang::findOrFail($id);
            $data = $request->validate([
                'nama' => 'sometimes',
                'deskripsi' => 'sometimes',
                'warna' => 'sometimes',
                'is_active' => 'sometimes|boolean',
            ]);
            $bidang->update($data);
            return response()->json([
                'status' => true,
                'message' => 'Bidang berhasil diupdate',
                'data' => $bidang
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Bidang tidak ditemukan',
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
            $bidang = MasterBidang::findOrFail($id);
            $bidang->delete();
            return response()->json([
                'status' => true,
                'message' => 'Bidang berhasil dihapus',
                'data' => null
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Bidang tidak ditemukan',
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

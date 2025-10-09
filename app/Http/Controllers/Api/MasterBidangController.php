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
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Post(
 *     path="/master-bidang",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterBidang"},
 *     summary="Tambah bidang",
 *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="application/json")),
 *     @OA\Response(response=201, description="Created")
 * )
 *
 * @OA\Put(
 *     path="/master-bidang/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterBidang"},
 *     summary="Update bidang",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="application/json")),
 *     @OA\Response(response=200, description="OK")
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

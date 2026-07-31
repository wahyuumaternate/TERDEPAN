<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterSubBidang;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="MasterSubBidang",
 *     description="API Master Sub Bidang"
 * )
 *
 * @OA\Get(
 *     path="/master-sub-bidang",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterSubBidang"},
 *     summary="List semua sub bidang",
 *
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Get(
 *     path="/master-sub-bidang/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterSubBidang"},
 *     summary="Detail sub bidang",
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID sub bidang",
 *
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="OK",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Detail sub bidang"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="bidang_id", type="integer", example=1),
 *                 @OA\Property(property="nama", type="string", example="Sub Bidang Data")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Sub bidang tidak ditemukan")
 * )
 *
 * @OA\Post(
 *     path="/master-sub-bidang",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterSubBidang"},
 *     summary="Tambah sub bidang",
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"bidang_id","nama"},
 *
 *             @OA\Property(property="bidang_id", type="integer", example=1),
 *             @OA\Property(property="nama", type="string", example="Sub Bidang Data")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Created",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Sub bidang berhasil ditambah"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="bidang_id", type="integer", example=1),
 *                 @OA\Property(property="nama", type="string", example="Sub Bidang Data")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=422, description="Validasi gagal")
 * )
 *
 * @OA\Put(
 *     path="/master-sub-bidang/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterSubBidang"},
 *     summary="Update sub bidang",
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID sub bidang",
 *
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="bidang_id", type="integer", example=1),
 *             @OA\Property(property="nama", type="string", example="Sub Bidang Data")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="OK",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Sub bidang berhasil diupdate"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="bidang_id", type="integer", example=1),
 *                 @OA\Property(property="nama", type="string", example="Sub Bidang Data")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Sub bidang tidak ditemukan"),
 *     @OA\Response(response=422, description="Validasi gagal")
 * )
 *
 * @OA\Delete(
 *     path="/master-sub-bidang/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterSubBidang"},
 *     summary="Hapus sub bidang",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *
 *     @OA\Response(response=200, description="OK")
 * )
 */
class MasterSubBidangController extends Controller
{
    public function index()
    {
        try {
            $data = MasterSubBidang::with('bidang')->get();

            return response()->json([
                'status' => true,
                'message' => 'List semua sub bidang',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $subBidang = MasterSubBidang::with('bidang')->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Detail sub bidang',
                'data' => $subBidang,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Sub bidang tidak ditemukan',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'bidang_id' => 'required|exists:master_bidang,id',
                'nama' => 'required|string|max:100',
            ]);
            $subBidang = MasterSubBidang::create($data);

            return response()->json([
                'status' => true,
                'message' => 'Sub bidang berhasil ditambah',
                'data' => $subBidang,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $subBidang = MasterSubBidang::findOrFail($id);
            $data = $request->validate([
                'bidang_id' => 'sometimes|exists:master_bidang,id',
                'nama' => 'sometimes|string|max:100',
            ]);
            $subBidang->update($data);

            return response()->json([
                'status' => true,
                'message' => 'Sub bidang berhasil diupdate',
                'data' => $subBidang,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Sub bidang tidak ditemukan',
                'data' => null,
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $subBidang = MasterSubBidang::findOrFail($id);
            $subBidang->delete();

            return response()->json([
                'status' => true,
                'message' => 'Sub bidang berhasil dihapus',
                'data' => null,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Sub bidang tidak ditemukan',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}

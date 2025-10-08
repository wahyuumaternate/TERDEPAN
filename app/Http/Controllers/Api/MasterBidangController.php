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
 *     path="/api/v1/master-bidang",
 *     tags={"MasterBidang"},
 *     summary="List semua bidang",
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/master-bidang/{id}",
 *     tags={"MasterBidang"},
 *     summary="Detail bidang",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/master-bidang",
 *     tags={"MasterBidang"},
 *     summary="Tambah bidang",
 *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="application/json")),
 *     @OA\Response(response=201, description="Created")
 * )
 *
 * @OA\Put(
 *     path="/api/v1/master-bidang/{id}",
 *     tags={"MasterBidang"},
 *     summary="Update bidang",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="application/json")),
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Delete(
 *     path="/api/v1/master-bidang/{id}",
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
        return response()->json(MasterBidang::with('pegawai')->get());
    }

    public function show($id)
    {
        $bidang = MasterBidang::with('pegawai')->findOrFail($id);
        return response()->json($bidang);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|unique:master_bidang,kode',
            'nama' => 'required',
            'deskripsi' => 'nullable',
            'warna' => 'nullable',
            'is_active' => 'boolean',
        ]);
        $bidang = MasterBidang::create($data);
        return response()->json($bidang, 201);
    }

    public function update(Request $request, $id)
    {
        $bidang = MasterBidang::findOrFail($id);
        $data = $request->validate([
            'nama' => 'sometimes',
            'deskripsi' => 'sometimes',
            'warna' => 'sometimes',
            'is_active' => 'sometimes|boolean',
        ]);
        $bidang->update($data);
        return response()->json($bidang);
    }

    public function destroy($id)
    {
        $bidang = MasterBidang::findOrFail($id);
        $bidang->delete();
        return response()->json(['message' => 'Deleted']);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterTtdDigital;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="MasterTtdDigital",
 *     description="API Master TTD Digital"
 * )
 *
 * @OA\Get(
 *     path="/api/v1/master-ttd-digital",
 *     tags={"MasterTtdDigital"},
 *     summary="List semua TTD Digital",
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/master-ttd-digital/{id}",
 *     tags={"MasterTtdDigital"},
 *     summary="Detail TTD Digital",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/master-ttd-digital",
 *     tags={"MasterTtdDigital"},
 *     summary="Tambah TTD Digital",
 *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="application/json")),
 *     @OA\Response(response=201, description="Created")
 * )
 *
 * @OA\Put(
 *     path="/api/v1/master-ttd-digital/{id}",
 *     tags={"MasterTtdDigital"},
 *     summary="Update TTD Digital",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="application/json")),
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Delete(
 *     path="/api/v1/master-ttd-digital/{id}",
 *     tags={"MasterTtdDigital"},
 *     summary="Hapus TTD Digital",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="OK")
 * )
 */
class MasterTtdDigitalController extends Controller
{
    public function index()
    {
        return response()->json(MasterTtdDigital::with('pegawai')->get());
    }

    public function show($id)
    {
        $ttd = MasterTtdDigital::with('pegawai')->findOrFail($id);
        return response()->json($ttd);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pegawai_id' => 'required|exists:master_pegawai,id',
            'file_path' => 'required',
            'file_hash' => 'required',
            'image_width' => 'nullable|integer',
            'image_height' => 'nullable|integer',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date',
        ]);
        $ttd = MasterTtdDigital::create($data);
        return response()->json($ttd, 201);
    }

    public function update(Request $request, $id)
    {
        $ttd = MasterTtdDigital::findOrFail($id);
        $data = $request->validate([
            'file_path' => 'sometimes',
            'file_hash' => 'sometimes',
            'image_width' => 'sometimes|integer',
            'image_height' => 'sometimes|integer',
            'valid_from' => 'sometimes|date',
            'valid_until' => 'sometimes|date',
        ]);
        $ttd->update($data);
        return response()->json($ttd);
    }

    public function destroy($id)
    {
        $ttd = MasterTtdDigital::findOrFail($id);
        $ttd->delete();
        return response()->json(['message' => 'Deleted']);
    }
}

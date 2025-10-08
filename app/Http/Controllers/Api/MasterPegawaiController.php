<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterPegawai;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="MasterPegawai",
 *     description="API Master Pegawai"
 * )
 *
 * @OA\Get(
 *     path="/api/v1/master-pegawai",
 *     tags={"MasterPegawai"},
 *     summary="List semua pegawai",
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/master-pegawai/{id}",
 *     tags={"MasterPegawai"},
 *     summary="Detail pegawai",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/master-pegawai",
 *     tags={"MasterPegawai"},
 *     summary="Tambah pegawai",
 *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="application/json")),
 *     @OA\Response(response=201, description="Created")
 * )
 *
 * @OA\Put(
 *     path="/api/v1/master-pegawai/{id}",
 *     tags={"MasterPegawai"},
 *     summary="Update pegawai",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="application/json")),
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Delete(
 *     path="/api/v1/master-pegawai/{id}",
 *     tags={"MasterPegawai"},
 *     summary="Hapus pegawai",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="OK")
 * )
 */
class MasterPegawaiController extends Controller
{
    public function index()
    {
        return response()->json(MasterPegawai::with(['jabatan', 'bidang', 'atasanLangsung', 'ttdDigital'])->get());
    }

    public function show($id)
    {
        $pegawai = MasterPegawai::with(['jabatan', 'bidang', 'atasanLangsung', 'ttdDigital'])->findOrFail($id);
        return response()->json($pegawai);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomor_identitas' => 'required|unique:master_pegawai,nomor_identitas',
            'tipe_identitas' => 'required',
            'nama' => 'required',
            'jabatan_id' => 'required|exists:master_jabatan,id',
            'bidang_id' => 'required|exists:master_bidang,id',
            'email' => 'required|email|unique:master_pegawai,email',
            'password' => 'required',
        ]);
        $data['password'] = bcrypt($data['password']);
        $pegawai = MasterPegawai::create($data);
        return response()->json($pegawai, 201);
    }

    public function update(Request $request, $id)
    {
        $pegawai = MasterPegawai::findOrFail($id);
        $data = $request->validate([
            'nama' => 'sometimes',
            'jabatan_id' => 'sometimes|exists:master_jabatan,id',
            'bidang_id' => 'sometimes|exists:master_bidang,id',
            'email' => 'sometimes|email|unique:master_pegawai,email,' . $id,
            'password' => 'sometimes',
        ]);
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        $pegawai->update($data);
        return response()->json($pegawai);
    }

    public function destroy($id)
    {
        $pegawai = MasterPegawai::findOrFail($id);
        $pegawai->delete();
        return response()->json(['message' => 'Deleted']);
    }
}

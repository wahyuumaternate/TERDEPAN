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
 *     path="/master-pegawai",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterPegawai"},
 *     summary="List semua pegawai",
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Get(
 *     path="/master-pegawai/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterPegawai"},
 *     summary="Detail pegawai",
 *     description="Mengambil detail data pegawai beserta relasi jabatan, bidang, atasan langsung, dan ttd digital.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID pegawai",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="OK",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Detail pegawai"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="nomor_identitas", type="string", example="197812312005011001"),
 *                 @OA\Property(property="tipe_identitas", type="string", example="NIP"),
 *                 @OA\Property(property="nama", type="string", example="Ahmad Pegawai"),
 *                 @OA\Property(property="email", type="string", example="pegawai@email.com"),
 *                 @OA\Property(property="jabatan", type="object"),
 *                 @OA\Property(property="bidang", type="object"),
 *                 @OA\Property(property="atasanLangsung", type="object"),
 *                 @OA\Property(property="ttdDigital", type="object")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=404, description="Pegawai tidak ditemukan")
 * )
 *
 * @OA\Post(
 *     path="/master-pegawai",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterPegawai"},
 *     summary="Tambah pegawai",
 *     description="Menambah data pegawai baru.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"nomor_identitas","tipe_identitas","nama","jabatan_id","bidang_id","jenis_kelamin","status_kepegawaian","email","password"},
 *             @OA\Property(property="nomor_identitas", type="string", example="197812312005011001"),
 *             @OA\Property(property="tipe_identitas", type="string", example="NIP"),
 *             @OA\Property(property="nama", type="string", example="Ahmad Pegawai"),
 *             @OA\Property(property="jabatan_id", type="integer", example=1),
 *             @OA\Property(property="bidang_id", type="integer", example=1),
 *             @OA\Property(property="jenis_kelamin", type="string", enum={"L","P"}, example="L"),
 *             @OA\Property(property="status_kepegawaian", type="string", example="Kontrak"),
 *             @OA\Property(property="email", type="string", example="pegawai@email.com"),
 *             @OA\Property(property="password", type="string", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Created",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Pegawai berhasil ditambah"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="nomor_identitas", type="string", example="197812312005011001"),
 *                 @OA\Property(property="tipe_identitas", type="string", example="NIP"),
 *                 @OA\Property(property="nama", type="string", example="Ahmad Pegawai"),
 *                 @OA\Property(property="email", type="string", example="pegawai@email.com"),
 *                 @OA\Property(property="jabatan_id", type="integer", example=1),
 *                 @OA\Property(property="bidang_id", type="integer", example=1)
 *             )
 *         )
 *     ),
 *     @OA\Response(response=422, description="Validasi gagal")
 * )
 *
 * 
 * @OA\Put(
 *     path="/master-pegawai/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"MasterPegawai"},
 *     summary="Update pegawai",
 *     description="Mengupdate data pegawai berdasarkan ID. Hanya field yang aman diubah: nama, jabatan_id, bidang_id, jenis_kelamin, status_kepegawaian, email, no_telepon, pangkat, golongan, gelar_depan, gelar_belakang, tanggal_lahir, alamat, status_aktif, tanggal_masuk, tanggal_keluar, password.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID pegawai",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="nama", type="string", example="Ahmad Pegawai"),
 *             @OA\Property(property="jabatan_id", type="integer", example=1),
 *             @OA\Property(property="bidang_id", type="integer", example=1),
 *             @OA\Property(property="jenis_kelamin", type="string", enum={"L","P"}, example="L"),
 *             @OA\Property(property="status_kepegawaian", type="string", example="Kontrak"),
 *             @OA\Property(property="email", type="string", example="pegawai@email.com"),
 *             @OA\Property(property="no_telepon", type="string", example="08123456789"),
 *             @OA\Property(property="pangkat", type="string", example="Penata"),
 *             @OA\Property(property="golongan", type="string", example="III/a"),
 *             @OA\Property(property="gelar_depan", type="string", example="Dr."),
 *             @OA\Property(property="gelar_belakang", type="string", example="M.Si."),
 *             @OA\Property(property="tanggal_lahir", type="string", format="date", example="1980-01-01"),
 *             @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 1"),
 *             @OA\Property(property="status_aktif", type="string", example="Aktif"),
 *             @OA\Property(property="tanggal_masuk", type="string", format="date", example="2020-01-01"),
 *             @OA\Property(property="tanggal_keluar", type="string", format="date", example="2030-01-01"),
 *             @OA\Property(property="atasan_langsung_id", type="integer", example=2, description="ID pegawai atasan langsung"),
 *             @OA\Property(property="password", type="string", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="OK",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Pegawai berhasil diupdate"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="nomor_identitas", type="string", example="197812312005011001"),
 *                 @OA\Property(property="tipe_identitas", type="string", example="NIP"),
 *                 @OA\Property(property="nama", type="string", example="Ahmad Pegawai"),
 *                 @OA\Property(property="email", type="string", example="pegawai@email.com"),
 *                 @OA\Property(property="jabatan_id", type="integer", example=1),
 *                 @OA\Property(property="bidang_id", type="integer", example=1)
 *             )
 *         )
 *     ),
 *     @OA\Response(response=404, description="Pegawai tidak ditemukan"),
 *     @OA\Response(response=422, description="Validasi gagal")
 * )
 *
 *
 * @OA\Delete(
 *     path="/master-pegawai/{id}",
 *     security={{"bearerAuth":{}}},
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
        try {
            $data = MasterPegawai::with(['jabatan', 'bidang', 'atasanLangsung', 'ttdDigital'])->get();
            return response()->json([
                'status' => true,
                'message' => 'List semua pegawai',
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
            $pegawai = MasterPegawai::with(['jabatan', 'bidang', 'atasanLangsung', 'ttdDigital'])->findOrFail($id);
            return response()->json([
                'status' => true,
                'message' => 'Detail pegawai',
                'data' => $pegawai
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Pegawai tidak ditemukan',
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
                'nomor_identitas' => 'required|unique:master_pegawai,nomor_identitas',
                'tipe_identitas' => 'required',
                'nama' => 'required',
                'jabatan_id' => 'required|exists:master_jabatan,id',
                'bidang_id' => 'required|exists:master_bidang,id',
                'jenis_kelamin' => 'required|in:L,P',
                'status_kepegawaian' => 'required',
                'email' => 'required|email|unique:master_pegawai,email',
                'password' => 'required',
            ]);
            $data['password'] = bcrypt($data['password']);
            $pegawai = MasterPegawai::create($data);
            return response()->json([
                'status' => true,
                'message' => 'Pegawai berhasil ditambah',
                'data' => $pegawai
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
            $pegawai = MasterPegawai::findOrFail($id);
            $data = $request->validate([
                'nama' => 'sometimes',
                'jabatan_id' => 'sometimes|exists:master_jabatan,id',
                'bidang_id' => 'sometimes|exists:master_bidang,id',
                'jenis_kelamin' => 'sometimes|in:L,P',
                'status_kepegawaian' => 'sometimes',
                'email' => 'sometimes|email|unique:master_pegawai,email,' . $id,
                'no_telepon' => 'sometimes',
                'pangkat' => 'sometimes',
                'golongan' => 'sometimes',
                'gelar_depan' => 'sometimes',
                'gelar_belakang' => 'sometimes',
                'tanggal_lahir' => 'sometimes|date',
                'alamat' => 'sometimes',
                'status_aktif' => 'sometimes',
                'tanggal_masuk' => 'sometimes|date',
                'tanggal_keluar' => 'sometimes|date',
                'atasan_langsung_id' => 'sometimes|nullable|exists:master_pegawai,id',
                'password' => 'sometimes',
            ]);
            if (isset($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            }
            $pegawai->update($data);
            return response()->json([
                'status' => true,
                'message' => 'Pegawai berhasil diupdate',
                'data' => $pegawai
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Pegawai tidak ditemukan',
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
            $pegawai = MasterPegawai::findOrFail($id);
            $pegawai->delete();
            return response()->json([
                'status' => true,
                'message' => 'Pegawai berhasil dihapus',
                'data' => null
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Pegawai tidak ditemukan',
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

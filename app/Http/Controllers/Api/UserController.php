<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="User",
 *     description="API Master Pegawai"
 * )
 *
 * @OA\Get(
 *     path="/users",
 *     security={{"bearerAuth":{}}},
 *     tags={"User"},
 *     summary="List semua pegawai",
 *
 *     @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Get(
 *     path="/users/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"User"},
 *     summary="Detail pegawai",
 *     description="Mengambil detail data pegawai beserta relasi profile (jabatan, bidang, atasan langsung).",
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID pegawai",
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
 *             @OA\Property(property="message", type="string", example="Detail pegawai"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="nama", type="string", example="Ahmad Pegawai"),
 *                 @OA\Property(property="email", type="string", example="pegawai@email.com"),
 *                 @OA\Property(property="profile", type="object",
 *                     @OA\Property(property="nomor_identitas", type="string", example="197812312005011001"),
 *                     @OA\Property(property="tipe_identitas", type="string", example="NIP"),
 *                     @OA\Property(property="jabatan", type="object"),
 *                     @OA\Property(property="bidang", type="object"),
 *                     @OA\Property(property="atasanLangsung", type="object")
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Pegawai tidak ditemukan")
 * )
 *
 * @OA\Post(
 *     path="/users",
 *     security={{"bearerAuth":{}}},
 *     tags={"User"},
 *     summary="Tambah pegawai",
 *     description="Menambah data pegawai baru (users + user_profiles).",
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"nomor_identitas","tipe_identitas","nama","jabatan_id","bidang_id","jenis_kelamin","status_kepegawaian","email","password"},
 *
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
 *
 *     @OA\Response(
 *         response=201,
 *         description="Created",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Pegawai berhasil ditambah"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="nama", type="string", example="Ahmad Pegawai"),
 *                 @OA\Property(property="email", type="string", example="pegawai@email.com")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=422, description="Validasi gagal")
 * )
 *
 * @OA\Put(
 *     path="/users/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"User"},
 *     summary="Update pegawai",
 *     description="Mengupdate data pegawai berdasarkan ID. Field identitas (nama, email, password) masuk ke tabel users, sisanya ke user_profiles.",
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID pegawai",
 *
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *
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
 *
 *     @OA\Response(
 *         response=200,
 *         description="OK",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Pegawai berhasil diupdate"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="nama", type="string", example="Ahmad Pegawai"),
 *                 @OA\Property(property="email", type="string", example="pegawai@email.com")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=404, description="Pegawai tidak ditemukan"),
 *     @OA\Response(response=422, description="Validasi gagal")
 * )
 *
 * @OA\Delete(
 *     path="/users/{id}",
 *     security={{"bearerAuth":{}}},
 *     tags={"User"},
 *     summary="Hapus pegawai",
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *
 *     @OA\Response(response=200, description="OK")
 * )
 */
class UserController extends Controller
{
    private const PROFILE_FIELDS = [
        'nomor_identitas', 'tipe_identitas', 'jabatan_id', 'bidang_id', 'sub_bidang_id',
        'jenis_kelamin', 'status_kepegawaian', 'status_aktif', 'no_telepon', 'pangkat',
        'golongan', 'gelar_depan', 'gelar_belakang', 'tanggal_lahir', 'alamat',
        'tanggal_masuk', 'tanggal_keluar', 'atasan_langsung_id',
    ];

    public function index()
    {
        try {
            $data = User::with(['profile.jabatan', 'profile.bidang', 'profile.atasanLangsung'])->get();

            return response()->json([
                'status' => true,
                'message' => 'List semua pegawai',
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
            $pegawai = User::with(['profile.jabatan', 'profile.bidang', 'profile.atasanLangsung'])->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Detail pegawai',
                'data' => $pegawai,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Pegawai tidak ditemukan',
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
                'nomor_identitas' => 'required|unique:user_profiles,nomor_identitas',
                'tipe_identitas' => 'required',
                'nama' => 'required',
                'jabatan_id' => 'required|exists:master_jabatan,id',
                'bidang_id' => 'required|exists:master_bidang,id',
                'jenis_kelamin' => 'required|in:L,P',
                'status_kepegawaian' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
            ]);

            $profileData = array_intersect_key($data, array_flip(self::PROFILE_FIELDS));

            $pegawai = User::create([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ]);
            $pegawai->profile()->create($profileData);
            $pegawai->load(['profile.jabatan', 'profile.bidang']);

            return response()->json([
                'status' => true,
                'message' => 'Pegawai berhasil ditambah',
                'data' => $pegawai,
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
            $pegawai = User::with('profile')->findOrFail($id);
            $data = $request->validate([
                'nama' => 'sometimes',
                'jabatan_id' => 'sometimes|exists:master_jabatan,id',
                'bidang_id' => 'sometimes|exists:master_bidang,id',
                'jenis_kelamin' => 'sometimes|in:L,P',
                'status_kepegawaian' => 'sometimes',
                'email' => 'sometimes|email|unique:users,email,'.$id,
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
                'atasan_langsung_id' => 'sometimes|nullable|exists:users,id',
                'password' => 'sometimes',
            ]);

            $userData = array_filter([
                'nama' => $data['nama'] ?? null,
                'email' => $data['email'] ?? null,
                'password' => isset($data['password']) ? bcrypt($data['password']) : null,
            ], fn ($value) => $value !== null);

            if (! empty($userData)) {
                $pegawai->update($userData);
            }

            $profileData = array_intersect_key($data, array_flip(self::PROFILE_FIELDS));
            if (! empty($profileData)) {
                $pegawai->profile()->updateOrCreate(['user_id' => $pegawai->id], $profileData);
            }

            $pegawai->load(['profile.jabatan', 'profile.bidang']);

            return response()->json([
                'status' => true,
                'message' => 'Pegawai berhasil diupdate',
                'data' => $pegawai,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Pegawai tidak ditemukan',
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
            $pegawai = User::findOrFail($id);
            $pegawai->delete();

            return response()->json([
                'status' => true,
                'message' => 'Pegawai berhasil dihapus',
                'data' => null,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Pegawai tidak ditemukan',
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

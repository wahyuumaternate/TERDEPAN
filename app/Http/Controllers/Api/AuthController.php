<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\MasterPegawai;

/**
 * 
 * @OA\Tag(
 *     name="Auth",
 *     description="API Authentication"
 * )
 * 
 *     @OA\Post(
 *     path="/login",
 *     tags={"Auth"},
 *     summary="Login user (MasterPegawai) menggunakan NIP atau NIK",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"nomor_identitas","password"},
 *             @OA\Property(property="nomor_identitas", type="string", example="197001011990011001"),
 *             @OA\Property(property="password", type="string", example="password123")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Login sukses, return token"),
 *     @OA\Response(response=401, description="Unauthorized")
 * )
 */
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nomor_identitas' => 'required',
            'password' => 'required',
        ]);

        // Cari berdasarkan NIP atau NIK
        $pegawai = MasterPegawai::where('nomor_identitas', $credentials['nomor_identitas'])
            ->first();

        if (!$pegawai || !Hash::check($credentials['password'], $pegawai->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'data' => null
            ], 401);
        }
        // Update last login info
        $pegawai->last_login_at = now();
        $pegawai->last_login_ip = $request->ip();
        $pegawai->save();

        $token = $pegawai->createToken('api-token')->plainTextToken;
        return response()->json([
            'status' => true,
            'message' => 'Login sukses',
            'data' => [
                'user' => $pegawai,
                'token' => $token,
            ]
        ]);
    }
}

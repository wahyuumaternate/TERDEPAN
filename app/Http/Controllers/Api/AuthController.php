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
 * @OA\Post(
 *     path="/login",
 *     tags={"Auth"},
 *     summary="Login user (MasterPegawai)",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email","password"},
 *             @OA\Property(property="email", type="string", example="admin@gmail.com"),
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
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $pegawai = MasterPegawai::where('email', $credentials['email'])->first();
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

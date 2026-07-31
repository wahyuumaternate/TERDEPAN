<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="API Authentication"
 *     )
 *
 *     @OA\Post(
 *     path="/login",
 *     tags={"Auth"},
 *     summary="Login user menggunakan email",
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"email","password"},
 *
 *             @OA\Property(property="email", type="string", example="pegawai@email.com"),
 *             @OA\Property(property="password", type="string", example="password123")
 *         )
 *     ),
 *
 *     @OA\Response(response=200, description="Login sukses, return token"),
 *     @OA\Response(response=401, description="Unauthorized")
 *     )
 *
 *    @OA\Delete(
 *     path="/logout",
 *     tags={"Auth"},
 *     security={{"bearerAuth":{}}},
 *     summary="Logout user (revoke token yang sedang dipakai)",
 *
 *     @OA\Response(response=200, description="Logout sukses"),
 *     @OA\Response(response=401, description="Unauthorized")
 *     )
 *
 *    @OA\Get(
 *     path="/me",
 *     tags={"Auth"},
 *     security={{"bearerAuth":{}}},
 *     summary="Data user yang sedang login",
 *
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=401, description="Unauthorized")
 *     )
 */
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'data' => null,
            ], 401);
        }

        // Update last login info (disimpan di user_profiles)
        $user->profile()->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login sukses',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user->load('profile'),
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logout sukses',
            'data' => null,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'status' => true,
            'message' => 'Data user yang sedang login',
            'data' => $request->user()->load('profile.jabatan', 'profile.bidang'),
        ]);
    }
}

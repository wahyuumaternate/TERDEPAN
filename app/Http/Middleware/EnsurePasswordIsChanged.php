<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memaksa pegawai yang masih pakai password default (must_change_password=true, lihat
 * migration add_must_change_password_to_users_table) untuk ganti password dulu sebelum
 * mengakses halaman lain — docs/flow/02-alur-autentikasi.md §Login Awal - Mandiri.
 */
class EnsurePasswordIsChanged
{
    /**
     * Route yang tetap boleh diakses meski must_change_password masih true, supaya
     * tidak terjadi redirect loop (halaman ganti password itu sendiri, endpoint submit-nya,
     * dan logout).
     */
    private const ROUTE_DIKECUALIKAN = ['password.force', 'password.update', 'logout'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password && ! $request->routeIs(...self::ROUTE_DIKECUALIKAN)) {
            return redirect()->route('password.force');
        }

        return $next($request);
    }
}

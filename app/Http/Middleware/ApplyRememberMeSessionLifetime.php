<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\SessionGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sesuaikan masa berlaku cookie session berdasarkan checkbox "Ingat saya" saat login:
 * 7 hari jika aktif, 1 hari jika tidak (menggantikan config/session.php:lifetime yang
 * statis). Harus jalan SEBELUM StartSession menuliskan cookie ke response — karena
 * StartSession membaca config('session.lifetime') saat itu juga (di ujung middleware
 * stack, setelah $next() kembali), override config di sini akan langsung terpakai.
 *
 * Deteksi "remember" tidak bisa pakai Auth::viaRemember() (itu hanya true persis pada
 * request yang di-auto-login ulang dari cookie remember, bukan di request-request
 * berikutnya) — jadi dicek dua kondisi:
 * - Cookie::hasQueued(): cookie "remember_web_..." baru saja di-queue oleh
 *   Auth::attempt() PERSIS pada request login itu sendiri — belum ada di
 *   $request->cookies (baru dikirim browser di request-request BERIKUTNYA).
 * - $request->cookies->has(): cookie itu sudah pernah dikirim balik oleh browser di
 *   request-request setelah login.
 */
class ApplyRememberMeSessionLifetime
{
    private const HARI_DIINGAT = 7;

    private const HARI_TIDAK_DIINGAT = 1;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Dicek SETELAH $next(), bukan sebelum — kalau ini request login, Auth::attempt()
        // (yang membuat sesi & meng-queue cookie remember) baru terjadi di dalam $next()
        // tadi (controller), jadi Auth::check() masih false kalau dicek sebelum $next().
        if (Auth::check()) {
            /** @var SessionGuard $guard */
            $guard = Auth::guard('web');
            $recallerName = $guard->getRecallerName();
            $diingat = $request->cookies->has($recallerName) || Cookie::hasQueued($recallerName);

            config(['session.lifetime' => 60 * 24 * ($diingat ? self::HARI_DIINGAT : self::HARI_TIDAK_DIINGAT)]);
        }

        return $response;
    }
}

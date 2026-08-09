<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi akses dokumentasi REST API (L5 Swagger, lihat config/l5-swagger.php) hanya
 * untuk pegawai yang sudah login dan berjabatan ADMIN. Selain itu di-404-kan (bukan 403)
 * supaya keberadaan URL dokumentasi tidak bocor ke pengguna yang tidak berwenang.
 */
class EnsureIsApiDocsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $kodeJabatan = $user?->profile?->jabatan?->kode;

        if (! $user || $kodeJabatan !== 'ADMIN') {
            abort(404);
        }

        return $next($request);
    }
}

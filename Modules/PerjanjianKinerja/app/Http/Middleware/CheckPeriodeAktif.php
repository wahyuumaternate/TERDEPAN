<?php

namespace Modules\PerjanjianKinerja\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPeriodeAktif
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tahun = $request->input('tahun', date('Y'));
        $periode = \Modules\PerjanjianKinerja\Models\PkPeriode::getPeriodeAktif($tahun);

        if (!$periode) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada periode pengisian PK yang sedang aktif'
                ], 403);
            }

            return redirect()->back()
                ->with('error', 'Tidak ada periode pengisian PK yang sedang aktif untuk tahun ' . $tahun);
        }

        // Pass periode to the request
        $request->merge(['periode_aktif' => $periode]);

        return $next($request);
    }
}

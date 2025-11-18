<?php

namespace Modules\TerminalData\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTerminalDataAccess
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (!$request->user()->hasPermissionTo($permission)) {
            abort(403, 'Anda tidak memiliki akses untuk fitur ini.');
        }

        return $next($request);
    }
}

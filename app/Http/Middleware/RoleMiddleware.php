<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user() || ! in_array($request->user()->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        if (($request->user()->status_akun ?? 'Aktif') !== 'Aktif') {
            Auth::logout();
            abort(403, 'Akun Anda sedang nonaktif. Hubungi admin sekolah.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictByKel
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'getKelGroupAttribute')) {
            return $next($request);
        }

        $group = $user->kel_group ?? 'admfak';
        if ($group === 'admfak') {
            return $next($request);
        }

        $path = trim($request->path(), '/');

        if (
            $path === 'logout'
            || str_starts_with($path, 'admin/keep-alive')
            || $request->routeIs('logout')
        ) {
            return $next($request);
        }

        $allowed = false;

        if ($group === 'cashless') {
            $allowed = $path === 'admin' || str_starts_with($path, 'admin/smartcard');
        } elseif ($group === 'kantin') {
            $allowed = $path === 'admin'
                || str_starts_with($path, 'admin/smartcard/transaksi-belanja')
                || str_starts_with($path, 'admin/smartcard/tap-belanja');
        }

        if ($allowed) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke halaman ini.'], 403);
        }

        $home = method_exists($user, 'defaultHomePath') ? $user->defaultHomePath() : '/admin';

        return redirect($home)->with('smartcard_error', 'Anda tidak memiliki akses ke menu tersebut.');
    }
}

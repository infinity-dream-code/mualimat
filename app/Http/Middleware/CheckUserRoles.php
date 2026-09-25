<?php

namespace App\Http\Middleware;

use App\Support\PersistentLogin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRoles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$params)
    {
        try {
            PersistentLogin::attemptRestore();
        } catch (\Throwable $e) {
            report($e);
        }

        if (!Auth::check()) {
            if (PersistentLogin::hasCookie()) {
                if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'message' => 'Memulihkan sesi',
                        'retry' => true,
                        'csrf' => $request->hasSession() ? csrf_token() : null,
                    ], 503);
                }

                if ($request->isMethod('GET') && !$request->cookies->get('_auth_retry')) {
                    return redirect($request->fullUrl())
                        ->withCookie(cookie('_auth_retry', '1', 1, null, null, false, true, false, 'lax'));
                }

                return redirect('/admin');
            }

            return redirect('login');
        }

        $user = Auth::user();

        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        foreach ($params as $param) {
            if (method_exists($user, 'hasRole') && $user->hasRole($param)) {
                return $next($request);
            }
        }

        abort(404, 'Halaman Tidak Ditemukan!');
    }
}

<?php

namespace App\Http\Middleware;

use App\Support\PersistentLogin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestorePersistentLogin
{
    /**
     * Restore Auth from persistent cookie when session is missing/broken.
     * Must never throw — a failure here must not become HTTP 500.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            PersistentLogin::attemptRestore();
        } catch (\Throwable $e) {
            report($e);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSession
{
    /**
     * Legacy middleware — jangan paksa redirect login.
     * Auth + PersistentLogin yang menangani sesi.
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}

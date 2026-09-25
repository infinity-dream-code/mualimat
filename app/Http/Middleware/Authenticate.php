<?php

namespace App\Http\Middleware;

use App\Support\PersistentLogin;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            PersistentLogin::attemptRestore();
        } catch (\Throwable $e) {
            report($e);
        }

        // Cookie ada tapi restore gagal (DB blip): retry GET sekali, jangan buang ke login
        if (!Auth::check() && PersistentLogin::hasCookie() && $request->isMethod('GET') && !$request->cookies->get('_auth_retry')) {
            return redirect($request->fullUrl())
                ->withCookie(cookie('_auth_retry', '1', 1, null, null, false, true, false, 'lax'));
        }

        return parent::handle($request, $next, ...$guards);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    protected function unauthenticated($request, array $guards)
    {
        try {
            PersistentLogin::attemptRestore();
        } catch (\Throwable $e) {
            report($e);
        }

        if (Auth::check()) {
            // restore berhasil di detik terakhir — jangan redirect login
            throw new HttpResponseException(
                redirect($request->fullUrl() ?: url('/admin'))
            );
        }

        // Cookie masih ada: AJAX jangan diarahkan ke login (bisa terasa "logout tiba-tiba")
        if (PersistentLogin::hasCookie()) {
            if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                throw new HttpResponseException(response()->json([
                    'message' => 'Memulihkan sesi',
                    'retry' => true,
                    'csrf' => $request->hasSession() ? csrf_token() : null,
                ], 503));
            }

            if ($request->isMethod('GET') && !$request->cookies->get('_auth_retry')) {
                throw new HttpResponseException(
                    redirect($request->fullUrl() ?: url('/admin'))
                        ->withCookie(cookie('_auth_retry', '1', 1, null, null, false, true, false, 'lax'))
                );
            }

            // Soft landing ke admin, bukan force login form
            throw new HttpResponseException(redirect('/admin'));
        }

        throw new AuthenticationException(
            'Unauthenticated.',
            $guards,
            $this->redirectTo($request)
        );
    }
}

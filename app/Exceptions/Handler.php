<?php

namespace App\Exceptions;

use App\Support\PersistentLogin;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): Response
    {
        if ($e instanceof TokenMismatchException) {
            return $this->recoverTokenMismatch($request);
        }

        if ($this->shouldRetryTransientGet($request, $e)) {
            return redirect($request->fullUrl())
                ->withCookie(cookie('_transient_retry', '1', 1, null, null, false, true, false, 'lax'));
        }

        // AJAX/JSON: jangan lempar HTML 500 untuk error transient — biarkan client retry
        if ($this->isTransient($e) && ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest')) {
            return response()->json([
                'message' => 'Server sibuk, silakan coba lagi',
                'retry' => true,
            ], 503);
        }

        return parent::render($request, $e);
    }

    private function recoverTokenMismatch(Request $request): Response
    {
        try {
            PersistentLogin::attemptRestore();
            if ($request->hasSession()) {
                $request->session()->regenerateToken();
            }
        } catch (Throwable $e) {
            report($e);
        }

        $csrf = null;
        try {
            $csrf = $request->hasSession() ? csrf_token() : null;
        } catch (Throwable $e) {
            $csrf = null;
        }

        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'message' => 'CSRF token mismatch',
                'csrf' => $csrf,
                'retry' => true,
            ], 419);
        }

        // Full-page: jangan tampilkan error 419 — kembali diam-diam
        $referer = $request->headers->get('Referer');
        $target = $referer ?: url('/admin');

        return redirect($target);
    }

    private function shouldRetryTransientGet(Request $request, Throwable $e): bool
    {
        if (!$request->isMethod('GET') || $request->ajax() || $request->expectsJson()) {
            return false;
        }

        if ($request->cookies->get('_transient_retry') === '1') {
            return false;
        }

        return $this->isTransient($e);
    }

    private function isTransient(Throwable $e): bool
    {
        $message = strtolower($e->getMessage());
        $needles = [
            'session is locked',
            'session store not set',
            'serialization failure',
            'deadlock found',
            'deadlock',
            'try restarting transaction',
            'server has gone away',
            'lost connection',
            'connection refused',
            'connection timed out',
            'error while reading greeting packet',
            'waiting for table metadata lock',
            'sqlstate[hy000]',
            'sqlstate[40001]',
            'unable to obtain lock',
            'too many connections',
            'broken pipe',
        ];

        foreach ($needles as $needle) {
            if (str_contains($message, $needle)) {
                return true;
            }
        }

        $previous = $e->getPrevious();
        if ($previous instanceof Throwable && $previous !== $e) {
            return $this->isTransient($previous);
        }

        return false;
    }
}

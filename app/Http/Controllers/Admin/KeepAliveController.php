<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\PersistentLogin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeepAliveController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            PersistentLogin::attemptRestore();

            if (Auth::check()) {
                PersistentLogin::set(Auth::user());
            }

            return response()->json([
                'ok' => true,
                'authenticated' => Auth::check(),
                'csrf' => csrf_token(),
            ]);
        } catch (\Throwable $e) {
            report($e);

            $csrf = '';
            try {
                $csrf = $request->hasSession() ? (string) csrf_token() : '';
            } catch (\Throwable $ignored) {
                $csrf = '';
            }

            // Jangan kembalikan 500 — keep-alive harus selalu "lembut"
            return response()->json([
                'ok' => false,
                'authenticated' => Auth::check(),
                'csrf' => $csrf,
            ]);
        }
    }
}

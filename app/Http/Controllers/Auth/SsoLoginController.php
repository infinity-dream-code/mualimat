<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CyberKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SsoLoginController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $token = (string) $request->query('token', '');

        if ($token === '') {
            return redirect()->route('login')->withErrors([
                'username' => 'Token SSO tidak valid.',
            ]);
        }

        $payload = $this->verifyToken($token);

        if ($payload === null) {
            Log::warning('SSO login rejected: invalid token');

            return redirect()->route('login')->withErrors([
                'username' => 'Sesi SSO tidak valid atau sudah kedaluwarsa. Silakan login kembali dari portal.',
            ]);
        }

        $username = trim((string) ($payload['sub'] ?? ''));

        if ($username === '') {
            return redirect()->route('login')->withErrors([
                'username' => 'Token SSO tidak berisi data pengguna.',
            ]);
        }

        $user = CyberKey::query()->where('users', $username)->first();

        if (! $user) {
            Log::warning('SSO login rejected: user not found', ['username' => $username]);

            return redirect()->route('login')->withErrors([
                'username' => 'Akun tidak ditemukan.',
            ]);
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect('/admin');
    }

    private function verifyToken(string $token): ?array
    {
        $secret = config('services.portal_sso.secret');
        if (empty($secret)) {
            return null;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;
        $signingInput = $headerEncoded . '.' . $payloadEncoded;
        $expectedSignature = $this->base64UrlEncode(
            hash_hmac('sha256', $signingInput, $secret, true)
        );

        if (! hash_equals($expectedSignature, $signatureEncoded)) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
        if (! is_array($payload)) {
            return null;
        }

        $now = time();
        if (($payload['exp'] ?? 0) < $now) {
            return null;
        }

        if (($payload['iat'] ?? 0) > ($now + 30)) {
            return null;
        }

        return $payload;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder > 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}

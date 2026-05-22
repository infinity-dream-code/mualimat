<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CyberKey;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected function validateLogin(Request $request): void
    {
        $rules = [
            "username" => "required|string",
            "password" => "required|string",
        ];

        if ($this->shouldVerifyTurnstile()) {
            $rules["cf-turnstile-response"] = "required|string";
        }

        $request->validate($rules, [
            "username.required" => "Silakan isi username.",
            "password.required" => "Silakan isi password.",
            "cf-turnstile-response.required" =>
                "Silakan selesaikan verifikasi captcha.",
        ]);
    }

    protected function attemptLogin(Request $request)
    {
        if ($this->shouldVerifyTurnstile()) {
            $this->verifyTurnstile($request);
        }

        $username = trim((string) $request->input("username"));
        $password = (string) $request->input("password");

        $user = CyberKey::query()->where("users", $username)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                "username" => [
                    "Username tidak ditemukan. Periksa kembali username Anda.",
                ],
            ]);
        }

        if (empty($user->password)) {
            throw ValidationException::withMessages([
                "password" => [
                    "Akun ini belum memiliki password. Hubungi administrator.",
                ],
            ]);
        }

        if (
            strtolower(md5($password)) !==
            strtolower(trim((string) $user->password))
        ) {
            throw ValidationException::withMessages([
                "password" => [
                    "Password salah. Periksa kembali password Anda.",
                ],
            ]);
        }

        $this->guard()->login($user);

        return true;
    }

    protected function verifyTurnstile(Request $request): void
    {
        $response = Http::asForm()->post(
            "https://challenges.cloudflare.com/turnstile/v0/siteverify",
            [
                "secret" => config("services.turnstile.secret_key"),
                "response" => $request->input("cf-turnstile-response"),
                "remoteip" => $request->ip(),
            ],
        );

        if (!($response->json("success") ?? false)) {
            throw ValidationException::withMessages([
                "turnstile" => [
                    "Verifikasi captcha gagal. Centang captcha lalu coba lagi.",
                ],
            ]);
        }
    }

    protected function shouldVerifyTurnstile(): bool
    {
        if (!config("services.turnstile.enabled", true)) {
            return false;
        }

        if (!filled(config("services.turnstile.secret_key"))) {
            return false;
        }

        $host = request()->getHost();

        if (
            in_array($host, ["localhost", "127.0.0.1"], true) ||
            str_ends_with($host, ".test") ||
            str_ends_with($host, ".local")
        ) {
            return false;
        }

        return true;
    }

    protected function redirectTo(): string
    {
        return "/admin";
    }

    public function __construct()
    {
        $this->middleware("guest")->except("logout");
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            "username" => [
                "Username atau password salah. Periksa kembali data login Anda.",
            ],
        ]);
    }

    public function username(): string
    {
        return "username";
    }

    public function reloadCaptcha()
    {
        return response()->json(["captcha" => captcha_src()]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect("/");
    }
}

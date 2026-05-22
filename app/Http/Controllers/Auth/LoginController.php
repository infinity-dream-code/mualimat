<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CyberKey;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected function validator(array $data)
    {
        return Validator::make($data, [
            "username" => "required|string",
            "password" => "required|string",
            "cf-turnstile-response" => "required|string",
        ]);
    }

    protected function attemptLogin(Request $request)
    {
        $response = Http::asForm()->post(
            "https://challenges.cloudflare.com/turnstile/v0/siteverify",
            [
                "secret" => config("services.turnstile.secret_key"),
                "response" => $request->input("cf-turnstile-response"),
                "remoteip" => $request->ip(),
            ],
        );

        $success = $response->json("success") ?? false;

        if (!$success) {
            throw ValidationException::withMessages([
                "turnstile" => __(
                    "Gagal melakukan verifikasi Captcha, silahkan coba lagi.",
                ),
            ]);
        }

        // $captchaData = [
        //     "captcha" => $request->input("captcha"), // Your CAPTCHA input field name
        // ];

        // if (!captcha_check($captchaData["captcha"])) {
        //     throw ValidationException::withMessages([
        //         //                'captcha' => [Lang::get('validation.captcha')],
        //         "captcha" => ["captcha salah, silahkan periksa kembali"],
        //     ]);
        // }

        $user = CyberKey::query()
            ->where("users", $request->input("username"))
            ->first();

        if (
            !$user ||
            empty($user->password) ||
            strtolower(md5((string) $request->input("password"))) !==
                strtolower((string) $user->password)
        ) {
            return false;
        }

        $this->guard()->login($user);

        return true;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected function redirectTo(): string
    {
        return "/admin";
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware("guest")->except("logout");
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            "password" => ["password anda salah, silahkan periksa kembali"],
            "username" => ["username anda salah, silahkan periksa kembali"],
        ]);
    }

    public function username(): string
    {
        return "users";
    }

    protected function credentials(Request $request): array
    {
        return [
            "users" => $request->input("username"),
            "password" => $request->input("password"),
        ];
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

    //    public function loginWithToken(Request $request)
    //    {
    //        $token = $request->query('token');
    //
    //        if (!$token) {
    //            return response()->json(['error' => 'Token is required'], 400);
    //        }
    //
    //        $user = User::where('login_token', $token)->first();
    //
    //        if (!$user) {
    //            return response()->json(['error' => 'Invalid token or user not found'], 401);
    //        }
    //
    //        if (!$user->hasRole('siswa')) {
    //            return redirect('/unauthorized')->with('error', 'You do not have the necessary role.');
    //        }
    //
    //        Auth::login($user);
    //
    //        // Optionally, invalidate or regenerate the token
    //        // $user->login_token = Str::random(60);
    //        // $user->save();
    //
    //        return redirect()->route('siswa.index');
    //    }
}

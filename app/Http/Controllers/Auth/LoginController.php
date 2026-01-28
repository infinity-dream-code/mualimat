<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Log\UserLog;
use App\Models\User;
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
            $this->username() => "required|string",
            "password" => "required|string",
            "cf-turnstile-response" => "required|string", // This enforces CAPTCHA validation
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

        // Continue with login attempt
        $authenticated = $this->guard()->attempt(
            $this->credentials($request),
            $request->filled("remember"),
        );

        //        if ($authenticated) {
        //            UserLog::createLog('login_menu', 'user_login',null, $request);
        //        }

        return $authenticated;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected function redirectTo()
    {
        // Check user role and return the appropriate redirect path
        if (auth()->user()->hasRole("siswa")) {
            return "/siswa";
        } elseif (auth()->user()->hasRole("admin")) {
            return "/admin";
        } elseif (auth()->user()->hasRole("super-admin")) {
            return "/admin";
        }
        return "/logout";
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

    public function username()
    {
        $login = request()->input("username");
        $field = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? "email"
            : "username";
        request()->merge([$field => $login]);
        return $field;
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

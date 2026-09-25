<?php

namespace App\Support;

use App\Models\CyberKey;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class PersistentLogin
{
    public const COOKIE = 'muallimat_keu';

    public static function minutes(): int
    {
        return max(1, (int) config('session.lifetime', 5256000));
    }

    public static function hasCookie(): bool
    {
        $raw = request()->cookie(self::COOKIE);

        return $raw !== null && $raw !== '';
    }

    public static function set(?Authenticatable $user = null): void
    {
        $user = $user ?: Auth::user();
        if (!$user) {
            return;
        }

        Cookie::queue(cookie(
            self::COOKIE,
            (string) $user->getAuthIdentifier(),
            self::minutes(),
            config('session.path', '/'),
            config('session.domain'),
            (bool) config('session.secure', false),
            true,
            false,
            config('session.same_site', 'lax') ?: 'lax'
        ));
    }

    public static function clear(): void
    {
        Cookie::queue(Cookie::forget(
            self::COOKIE,
            config('session.path', '/'),
            config('session.domain')
        ));
    }

    /**
     * Restore Auth from cookie when session is missing/broken.
     * Never clears the cookie on transient DB failures (that would force logout).
     */
    public static function attemptRestore(): bool
    {
        if (Auth::check()) {
            return true;
        }

        if (!self::hasCookie()) {
            return false;
        }

        $raw = request()->cookie(self::COOKIE);
        $id = is_numeric($raw) ? (int) $raw : 0;
        if ($id <= 0) {
            // cookie corrupt — only then remove
            self::clear();
            return false;
        }

        try {
            $user = CyberKey::query()->whereKey($id)->first();
        } catch (\Throwable $e) {
            // DB blip: keep cookie, do not logout
            Log::warning('PersistentLogin restore deferred (DB)', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }

        if (!$user) {
            self::clear();
            return false;
        }

        try {
            Auth::login($user, false);
            self::set($user);
            return true;
        } catch (\Throwable $e) {
            Log::warning('PersistentLogin Auth::login failed', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }
}

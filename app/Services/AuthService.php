<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    private const MAX_ATTEMPTS = 5;

    /**
     * @throws ValidationException on bad credentials, no panel access, or too many attempts
     */
    public function attempt(string $email, string $password, bool $remember, string $ip): void
    {
        $key = Str::lower($email).'|'.$ip;

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)]),
            ]);
        }

        // A user without a role has no panel access, so treat it like a bad password (no account probing).
        $loggedIn = Auth::attemptWhen(
            ['email' => $email, 'password' => $password],
            fn ($user) => $user->role_id !== null,
            $remember,
        );

        if (! $loggedIn) {
            RateLimiter::hit($key);

            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        RateLimiter::clear($key);
        session()->regenerate();
    }
}

<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /** @throws ValidationException */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
        $email = Str::lower((string) $this->input('email'));
        $user = User::where('email', $email)->first();

        if ($user?->locked_until && now()->lt($user->locked_until)) {
            event(new Lockout($this));
            throw ValidationException::withMessages([
                'email' => 'Sign-in is temporarily locked after three failed attempts. Try again in 15 minutes.',
            ]);
        }

        if ($user?->locked_until && now()->gte($user->locked_until)) {
            $user->forceFill(['failed_login_attempts' => 0, 'locked_until' => null])->save();
        }

        if (! Auth::attempt(['email' => $email, 'password' => $this->input('password')], $this->boolean('remember'))) {
            if ($user) {
                $attempts = min(3, (int) $user->failed_login_attempts + 1);
                $lockedUntil = $attempts >= 3 ? now()->addMinutes(15) : null;
                $user->forceFill(['failed_login_attempts' => $attempts, 'locked_until' => $lockedUntil])->save();
            }

            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if ($user && ($user->failed_login_attempts > 0 || $user->locked_until !== null)) {
            $user->forceFill(['failed_login_attempts' => 0, 'locked_until' => null])->save();
        }

        RateLimiter::clear($this->throttleKey());
    }

    /** @throws ValidationException */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower((string) $this->input('email')).'|'.$this->ip());
    }
}

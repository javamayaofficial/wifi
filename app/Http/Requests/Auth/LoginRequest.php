<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['nullable', 'string', 'max:255', 'required_without:email'],
            'email' => ['nullable', 'string', 'max:255', 'required_without:login'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->loginIdentifier();
        $password = $this->string('password')->toString();
        $remember = $this->boolean('remember');

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $ok = Auth::attempt(['email' => $login, 'password' => $password], $remember);
        } elseif ($this->looksLikePhone($login)) {
            $user = $this->findUserByPhone($login);
            $ok = $user && Hash::check($password, $user->password);

            if ($ok) {
                Auth::login($user, $remember);
            }
        } else {
            $ok = Auth::attempt(['username' => $login, 'password' => $password], $remember);
        }

        if (! $ok) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->loginIdentifier()).'|'.$this->ip());
    }

    protected function loginIdentifier(): string
    {
        return Str::lower(trim((string) ($this->input('login') ?: $this->input('email'))));
    }

    protected function looksLikePhone(string $login): bool
    {
        return preg_match('/^[0-9+\\-\\s()]+$/', $login) === 1;
    }

    protected function findUserByPhone(string $login): ?User
    {
        $normalized = $this->normalizePhone($login);

        return User::query()
            ->whereNotNull('phone')
            ->get()
            ->first(fn (User $user) => $this->normalizePhone((string) $user->phone) === $normalized);
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '62' . substr($phone, 1);
        }

        if (str_starts_with($phone, '8')) {
            return '62' . $phone;
        }

        return $phone;
    }
}

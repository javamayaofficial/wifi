<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Notifications\WhatsAppGatewayService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class WhatsappOtpLoginService
{
    public function __construct(
        protected WhatsAppGatewayService $whatsapp,
    ) {}

    public function requestOtp(string $phone, string $ipAddress): array
    {
        $normalized = $this->normalizePhone($phone);

        if (! $normalized) {
            return ['ok' => false, 'error' => 'Nomor WhatsApp belum valid.'];
        }

        $match = $this->findUserByPhone($normalized);

        if (! $match['ok']) {
            return $match;
        }

        $user = $match['user'];

        if (! $user->is_active) {
            return ['ok' => false, 'error' => 'Akun admin ini sedang nonaktif.'];
        }

        $sendKey = $this->sendThrottleKey($normalized, $ipAddress);
        $cooldown = (int) config('threfnet.auth_otp.resend_cooldown', 60);

        if (RateLimiter::tooManyAttempts($sendKey, 1)) {
            $seconds = RateLimiter::availableIn($sendKey);

            return [
                'ok' => false,
                'error' => "OTP baru bisa diminta lagi dalam {$seconds} detik.",
            ];
        }

        $code = (string) random_int(100000, 999999);
        $ttlMinutes = (int) config('threfnet.auth_otp.ttl_minutes', 5);

        $result = $this->whatsapp->send(
            $user->phone ?: $phone,
            "Kode OTP masuk THRE.F.NET: {$code}\nBerlaku {$ttlMinutes} menit.\nJangan bagikan kode ini kepada siapa pun."
        );

        if (! ($result['ok'] ?? false)) {
            return ['ok' => false, 'error' => $result['error'] ?? 'Gagal mengirim OTP WhatsApp.'];
        }

        Cache::put($this->cacheKey($normalized), [
            'user_id' => $user->id,
            'code' => Hash::make($code),
            'phone' => $normalized,
        ], now()->addMinutes($ttlMinutes));

        RateLimiter::hit($sendKey, $cooldown);

        return [
            'ok' => true,
            'masked_phone' => $this->maskPhone($normalized),
        ];
    }

    public function verifyOtp(string $phone, string $code, string $ipAddress): array
    {
        $normalized = $this->normalizePhone($phone);

        if (! $normalized) {
            return ['ok' => false, 'error' => 'Nomor WhatsApp belum valid.'];
        }

        $verifyKey = $this->verifyThrottleKey($normalized, $ipAddress);
        $maxAttempts = (int) config('threfnet.auth_otp.max_verify_attempts', 5);

        if (RateLimiter::tooManyAttempts($verifyKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($verifyKey);

            return [
                'ok' => false,
                'error' => "Terlalu banyak percobaan OTP. Coba lagi dalam {$seconds} detik.",
            ];
        }

        $payload = Cache::get($this->cacheKey($normalized));

        if (! is_array($payload) || empty($payload['code']) || empty($payload['user_id'])) {
            return ['ok' => false, 'error' => 'OTP tidak ditemukan atau sudah kedaluwarsa.'];
        }

        if (! Hash::check($code, $payload['code'])) {
            RateLimiter::hit($verifyKey, 300);

            return ['ok' => false, 'error' => 'Kode OTP salah.'];
        }

        $user = User::query()->find($payload['user_id']);

        if (! $user || ! $user->is_active) {
            return ['ok' => false, 'error' => 'Akun admin tidak tersedia untuk login OTP.'];
        }

        Cache::forget($this->cacheKey($normalized));
        RateLimiter::clear($verifyKey);

        return ['ok' => true, 'user' => $user];
    }

    protected function findUserByPhone(string $normalized): array
    {
        $users = User::query()
            ->whereNotNull('phone')
            ->get()
            ->filter(fn (User $user) => $this->normalizePhone($user->phone) === $normalized)
            ->values();

        if ($users->isEmpty()) {
            return ['ok' => false, 'error' => 'Nomor WhatsApp admin tidak ditemukan.'];
        }

        if ($users->count() > 1) {
            return ['ok' => false, 'error' => 'Nomor WhatsApp ini dipakai lebih dari satu akun admin.'];
        }

        return ['ok' => true, 'user' => $users->first()];
    }

    protected function cacheKey(string $normalized): string
    {
        return 'auth:otp:' . sha1($normalized);
    }

    protected function sendThrottleKey(string $normalized, string $ipAddress): string
    {
        return 'auth:otp:send:' . sha1($normalized . '|' . $ipAddress);
    }

    protected function verifyThrottleKey(string $normalized, string $ipAddress): string
    {
        return 'auth:otp:verify:' . sha1($normalized . '|' . $ipAddress);
    }

    protected function normalizePhone(?string $phone): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) $phone);

        if (! $phone) {
            return null;
        }

        if (str_starts_with($phone, '0')) {
            return '62' . substr($phone, 1);
        }

        if (str_starts_with($phone, '8')) {
            return '62' . $phone;
        }

        if (str_starts_with($phone, '62')) {
            return $phone;
        }

        return null;
    }

    protected function maskPhone(string $phone): string
    {
        if (strlen($phone) <= 6) {
            return $phone;
        }

        return substr($phone, 0, 4) . str_repeat('*', max(strlen($phone) - 6, 2)) . substr($phone, -2);
    }
}

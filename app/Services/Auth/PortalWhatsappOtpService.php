<?php

namespace App\Services\Auth;

use App\Models\Customer;
use App\Services\Notifications\WhatsAppGatewayService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class PortalWhatsappOtpService
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

        $match = $this->findCustomerByPhone($normalized);

        if (! $match['ok']) {
            return $match;
        }

        $customer = $match['customer'];
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
            $customer->phone ?: $phone,
            "Kode OTP portal pelanggan THRE.F.NET: {$code}\nBerlaku {$ttlMinutes} menit.\nJangan bagikan kode ini kepada siapa pun."
        );

        if (! ($result['ok'] ?? false)) {
            return ['ok' => false, 'error' => $result['error'] ?? 'Gagal mengirim OTP WhatsApp.'];
        }

        Cache::put($this->cacheKey($normalized), [
            'customer_id' => $customer->id,
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

        if (! is_array($payload) || empty($payload['code']) || empty($payload['customer_id'])) {
            return ['ok' => false, 'error' => 'OTP tidak ditemukan atau sudah kedaluwarsa.'];
        }

        if (! Hash::check($code, $payload['code'])) {
            RateLimiter::hit($verifyKey, 300);

            return ['ok' => false, 'error' => 'Kode OTP salah.'];
        }

        $customer = Customer::query()->find($payload['customer_id']);

        if (! $customer) {
            return ['ok' => false, 'error' => 'Akun pelanggan tidak ditemukan.'];
        }

        Cache::forget($this->cacheKey($normalized));
        RateLimiter::clear($verifyKey);

        return ['ok' => true, 'customer' => $customer];
    }

    protected function findCustomerByPhone(string $normalized): array
    {
        $customers = Customer::query()
            ->whereNotNull('phone')
            ->get()
            ->filter(fn (Customer $customer) => $this->normalizePhone($customer->phone) === $normalized)
            ->values();

        if ($customers->isEmpty()) {
            return ['ok' => false, 'error' => 'Nomor WhatsApp pelanggan tidak ditemukan.'];
        }

        if ($customers->count() > 1) {
            return ['ok' => false, 'error' => 'Nomor WhatsApp ini dipakai lebih dari satu pelanggan.'];
        }

        return ['ok' => true, 'customer' => $customers->first()];
    }

    protected function cacheKey(string $normalized): string
    {
        return 'portal:otp:' . sha1($normalized);
    }

    protected function sendThrottleKey(string $normalized, string $ipAddress): string
    {
        return 'portal:otp:send:' . sha1($normalized . '|' . $ipAddress);
    }

    protected function verifyThrottleKey(string $normalized, string $ipAddress): string
    {
        return 'portal:otp:verify:' . sha1($normalized . '|' . $ipAddress);
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

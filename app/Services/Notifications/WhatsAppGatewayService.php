<?php

namespace App\Services\Notifications;

use App\Models\Setting;
use GuzzleHttp\Client;

/**
 * WhatsAppGatewayService — SKELETON siap pakai.
 * Gateway WA dikembangkan sendiri; tinggal ganti endpoint + kontrak field.
 *
 * Membaca konfigurasi dari tabel settings (bila diisi via dashboard),
 * fallback ke config/.env untuk gateway sendiri maupun Fonnte.
 */
class WhatsAppGatewayService
{
    public function activeChannel(): string
    {
        return $this->provider() === 'fonnte' ? 'fonnte' : 'gateway';
    }

    public function send(string $phone, string $message): array
    {
        return $this->provider() === 'fonnte'
            ? $this->sendViaFonnte($phone, $message)
            : $this->sendViaGateway($phone, $message);
    }

    protected function provider(): string
    {
        return Setting::get('whatsapp_provider', config('threfnet.whatsapp.provider', 'gateway'));
    }

    protected function sendViaGateway(string $phone, string $message): array
    {
        $url    = Setting::get('whatsapp_gateway_url', config('threfnet.whatsapp.gateway_url'));
        $apiKey = Setting::get('whatsapp_api_key', config('threfnet.whatsapp.api_key'));

        if (! $url) {
            return ['ok' => false, 'error' => 'WHATSAPP_GATEWAY_URL belum dikonfigurasi.'];
        }

        try {
            $response = $this->http()->post($url, [
                'form_params' => [
                    // >>> Sesuaikan nama field ini dengan kontrak gateway Anda nanti <<<
                    'api_key' => $apiKey,
                    'phone'   => $this->normalizePhone($phone),
                    'message' => $message,
                ],
            ]);

            $status = $response->getStatusCode();
            return [
                'ok'   => $status >= 200 && $status < 300,
                'body' => (string) $response->getBody(),
            ];
        } catch (\Throwable $e) {
            report($e);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    protected function sendViaFonnte(string $phone, string $message): array
    {
        $token = Setting::get('fonnte_token', config('threfnet.whatsapp.fonnte_token'));
        $url   = Setting::get('fonnte_url', config('threfnet.whatsapp.fonnte_url'));
        $cc    = (string) Setting::get('whatsapp_country_code', config('threfnet.whatsapp.country_code', '62'));

        if (! $token) {
            return ['ok' => false, 'error' => 'Token Fonnte belum dikonfigurasi.'];
        }

        try {
            $response = $this->http()->post($url, [
                'headers' => [
                    'Authorization' => $token,
                ],
                'form_params' => [
                    'target'      => $this->normalizePhoneForFonnte($phone, $cc),
                    'message'     => $message,
                    'countryCode' => $cc,
                ],
            ]);

            $status = $response->getStatusCode();
            $body   = (string) $response->getBody();
            $json   = json_decode($body, true);
            $ok     = $status >= 200 && $status < 300;

            if (is_array($json) && array_key_exists('status', $json)) {
                $ok = $ok && (bool) $json['status'];
            }

            return [
                'ok'    => $ok,
                'body'  => $body,
                'error' => $ok ? null : ($json['reason'] ?? $json['detail'] ?? null),
            ];
        } catch (\Throwable $e) {
            report($e);

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }

    protected function normalizePhoneForFonnte(string $phone, string $countryCode): string
    {
        $phone = preg_replace('/\D+/', '', $phone);

        if ($countryCode === '0') {
            return $this->normalizePhone($phone);
        }

        if (str_starts_with($phone, $countryCode)) {
            return '0' . substr($phone, strlen($countryCode));
        }

        return $phone;
    }

    protected function http(): Client
    {
        return new Client(['timeout' => 15]);
    }
}

<?php

namespace App\Services\Notifications;

use App\Models\Setting;
use GuzzleHttp\Client;

/**
 * WhatsAppGatewayService — SKELETON siap pakai.
 * Gateway WA dikembangkan sendiri; tinggal ganti endpoint + kontrak field.
 *
 * Membaca konfigurasi dari tabel settings (bila diisi via dashboard),
 * fallback ke config/.env (WHATSAPP_GATEWAY_URL, WHATSAPP_API_KEY).
 */
class WhatsAppGatewayService
{
    public function send(string $phone, string $message): array
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

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }

    protected function http(): Client
    {
        return new Client(['timeout' => 15]);
    }
}

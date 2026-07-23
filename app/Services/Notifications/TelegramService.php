<?php

namespace App\Services\Notifications;

use App\Models\Setting;
use GuzzleHttp\Client;

/**
 * Alert internal ke admin/owner (router down, backup gagal, dsb).
 * Berbeda dari notifikasi pelanggan yang lewat WA/Email.
 */
class TelegramService
{
    public function send(string $message): array
    {
        $token  = Setting::get('telegram_bot_token');
        $chatId = Setting::get('telegram_chat_id');

        if (! $token || ! $chatId) {
            return ['ok' => false, 'error' => 'Telegram belum dikonfigurasi.'];
        }

        try {
            $response = (new Client(['timeout' => 15]))->post(
                "https://api.telegram.org/bot{$token}/sendMessage",
                ['form_params' => [
                    'chat_id'    => $chatId,
                    'text'       => $message,
                    'parse_mode' => 'HTML',
                ]]
            );

            $status = $response->getStatusCode();

            return ['ok' => $status >= 200 && $status < 300, 'body' => (string) $response->getBody()];
        } catch (\Throwable $e) {
            report($e);

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}

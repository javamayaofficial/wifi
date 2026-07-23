<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Services\Mikrotik\MikrotikService;
use App\Services\Notifications\TelegramService;
use Illuminate\Console\Command;

/**
 * Cek semua router tiap beberapa menit. Kirim alert Telegram saat router
 * TURUN dan saat PULIH.
 *
 * Tanpa ini, biasanya Anda baru tahu router mati setelah ditelepon pelanggan.
 */
class CheckRouterHealth extends Command
{
    protected $signature = 'threfnet:check-routers';
    protected $description = 'THRE.F.NET: cek koneksi semua router & kirim alert bila turun';

    /** Jangan spam: alert ulang paling cepat setiap 30 menit selama masih down. */
    protected int $repeatAlertMinutes = 30;

    public function handle(TelegramService $telegram): int
    {
        foreach (Router::all() as $router) {
            $up = (new MikrotikService($router))->testConnection();

            $wasUp = (bool) $router->is_up;

            $router->forceFill([
                'is_up'           => $up,
                'last_checked_at' => now(),
            ]);

            if (! $up) {
                if ($wasUp) {
                    $router->down_since = now();
                }

                $needAlert = ! $router->alert_sent_at
                    || $router->alert_sent_at->diffInMinutes(now()) >= $this->repeatAlertMinutes;

                if ($needAlert) {
                    $sejak = $router->down_since?->diffForHumans() ?? 'baru saja';

                    $telegram->send(
                        "🔴 <b>ROUTER DOWN</b>\n"
                        . "Router: <b>{$router->name}</b> ({$router->ip})\n"
                        . "Turun sejak: {$sejak}\n"
                        . 'THRE.F.NET Billing System'
                    );

                    $router->alert_sent_at = now();
                }

                $this->error("DOWN: {$router->name} ({$router->ip})");
            } else {
                if (! $wasUp) {
                    $durasi = $router->down_since?->diffForHumans(null, true) ?? '-';

                    $telegram->send(
                        "🟢 <b>ROUTER PULIH</b>\n"
                        . "Router: <b>{$router->name}</b> ({$router->ip})\n"
                        . "Lama gangguan: {$durasi}\n"
                        . 'THRE.F.NET Billing System'
                    );
                }

                $router->down_since    = null;
                $router->alert_sent_at = null;

                $this->line("OK  : {$router->name}");
            }

            $router->saveQuietly();
        }

        return self::SUCCESS;
    }
}

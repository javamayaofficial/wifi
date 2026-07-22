<?php

namespace App\Listeners;

use App\Events\CustomerExpiredEvent;
use App\Jobs\SendEmailNotification;
use App\Jobs\SendWhatsAppNotification;
use App\Services\Notifications\MailketingService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * SendExpiredNotificationListener — kirim peringatan isolir via WA + Email.
 * Template: "THRE.F.NET - Internet Terisolir".
 */
class SendExpiredNotificationListener implements ShouldQueue
{
    public int $tries = 3;
    public int $backoff = 30;

    public function handle(CustomerExpiredEvent $event): void
    {
        $customer = $event->customer;

        $waMessage = "[THRE.F.NET - Internet Terisolir]\n"
            . "Halo {$customer->name}, layanan internet Anda ({$customer->plan->name}) "
            . "telah jatuh tempo dan untuk sementara kami isolasi. "
            . "Silakan lakukan pembayaran agar layanan aktif kembali. Terima kasih.";

        $emailHtml = MailketingService::template(
            'THRE.F.NET - Internet Terisolir',
            "<p>Halo <b>{$customer->name}</b>,</p>"
            . "<p>Layanan internet THRE.F.NET paket <b>{$customer->plan->name}</b> Anda "
            . "telah <b>jatuh tempo</b> dan untuk sementara <b>diisolasi</b>.</p>"
            . "<p>Silakan lakukan pembayaran agar layanan aktif kembali secara otomatis.</p>"
        );

        SendWhatsAppNotification::dispatch($customer, $waMessage);
        SendEmailNotification::dispatch($customer, 'THRE.F.NET - Internet Terisolir', $emailHtml);
    }
}

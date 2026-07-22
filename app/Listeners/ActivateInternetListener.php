<?php

namespace App\Listeners;

use App\Events\PaymentSuccessEvent;
use App\Jobs\SendEmailNotification;
use App\Jobs\SendWhatsAppNotification;
use App\Services\Mikrotik\MikrotikService;
use App\Services\Notifications\MailketingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * ActivateInternetListener — dijalankan asinkron (queue).
 * 1) enable pelanggan di MikroTik
 * 2) kirim notifikasi WA + Email konfirmasi
 */
class ActivateInternetListener implements ShouldQueue
{
    public int $tries = 3;
    public int $backoff = 30;

    public function handle(PaymentSuccessEvent $event): void
    {
        $customer = $event->customer;

        try {
            MikrotikService::forCustomer($customer)->enableUser($customer);
            $customer->update(['status' => 'active']);
        } catch (\Throwable $e) {
            Log::error('THRE.F.NET: gagal aktivasi MikroTik', [
                'customer_id' => $customer->id,
                'error'       => $e->getMessage(),
            ]);
            throw $e; // biarkan queue retry
        }

        $waMessage = "Halo {$customer->name}, pembayaran Anda telah kami terima. "
            . "Layanan internet THRE.F.NET ({$customer->plan->name}) AKTIF hingga "
            . $customer->expired_date->translatedFormat('d F Y') . ". Terima kasih.";

        $emailHtml = MailketingService::template(
            'Pembayaran Diterima — Layanan Aktif',
            "<p>Halo <b>{$customer->name}</b>,</p>"
            . "<p>Pembayaran Anda telah kami terima dan layanan internet THRE.F.NET "
            . "paket <b>{$customer->plan->name}</b> telah <b>aktif</b>.</p>"
            . "<p>Berlaku hingga: <b>" . $customer->expired_date->translatedFormat('d F Y') . "</b></p>"
        );

        SendWhatsAppNotification::dispatch($customer, $waMessage);
        SendEmailNotification::dispatch($customer, 'THRE.F.NET - Pembayaran Diterima', $emailHtml);
    }
}

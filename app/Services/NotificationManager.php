<?php

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use App\Models\Customer;
use App\Models\NotificationLog;
use App\Services\Notifications\MailketingService;
use App\Services\Notifications\WhatsAppGatewayService;

/**
 * NotificationManager — orkestrasi dua kanal (WhatsApp + Email),
 * sekaligus mencatat ke thre_notifications_log.
 */
class NotificationManager implements NotificationServiceInterface
{
    public function __construct(
        protected WhatsAppGatewayService $whatsapp,
        protected MailketingService $mail,
    ) {}

    public function sendWhatsApp(string $phone, string $message, ?Customer $customer = null, ?string $context = null): bool
    {
        $result = $this->whatsapp->send($phone, $message);
        $this->log($customer, 'whatsapp', $this->whatsapp->activeChannel(), $result, $context);
        return (bool) ($result['ok'] ?? false);
    }

    public function sendEmail(string $to, string $subject, string $htmlContent, ?Customer $customer = null, ?string $context = null): bool
    {
        $result = $this->mail->send($to, $subject, $htmlContent);
        $this->log($customer, 'email', 'mailketing', $result, $context);
        return (bool) ($result['ok'] ?? false);
    }

    /** Kirim ke kedua kanal sekaligus bila data pelanggan tersedia. */
    public function notifyCustomer(Customer $customer, string $subject, string $waMessage, string $emailHtml): void
    {
        if ($customer->phone) {
            $this->sendWhatsApp($customer->phone, $waMessage, $customer);
        }
        if ($customer->email) {
            $this->sendEmail($customer->email, $subject, $emailHtml, $customer);
        }
    }

    protected function log(?Customer $customer, string $type, string $channel, array $result, ?string $context = null): void
    {
        NotificationLog::create([
            'customer_id' => $customer?->id,
            'type'        => $type,
            'channel'     => $channel,
            'context'     => $context,
            'status'      => ($result['ok'] ?? false) ? 'sent' : 'failed',
            'sent_at'     => now(),
            'error'       => $result['error'] ?? null,
        ]);
    }
}

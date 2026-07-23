<?php

namespace App\Contracts;

use App\Models\Customer;

interface NotificationServiceInterface
{
    /** Kirim pesan WhatsApp ke nomor pelanggan. */
    public function sendWhatsApp(string $phone, string $message, ?Customer $customer = null, ?string $context = null): bool;

    /** Kirim email (HTML) ke alamat pelanggan. */
    public function sendEmail(string $to, string $subject, string $htmlContent, ?Customer $customer = null, ?string $context = null): bool;
}

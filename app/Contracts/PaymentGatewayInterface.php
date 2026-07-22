<?php

namespace App\Contracts;

use App\Models\Transaction;
use App\Services\Payments\DTO\PaymentInitResult;
use App\Services\Payments\DTO\PaymentStatus;
use App\Services\Payments\DTO\WebhookResult;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /** Mulai pembayaran; kembalikan redirect_url / VA / QR / instruksi. */
    public function initiatePayment(Transaction $transaction): PaymentInitResult;

    /** Validasi + parsing webhook masuk dari gateway. */
    public function handleWebhook(Request $request): WebhookResult;

    /** Cek status transaksi langsung ke gateway (fallback/polling). */
    public function checkStatus(string $orderId): PaymentStatus;

    /** Metode yang didukung, mis. ['qris','va_bca']. */
    public function getSupportedMethods(): array;

    /** Definisi field konfigurasi untuk form Settings di dashboard. */
    public function getConfigFields(): array;
}

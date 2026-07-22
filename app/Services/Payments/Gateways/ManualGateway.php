<?php

namespace App\Services\Payments\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Transaction;
use App\Services\Payments\DTO\PaymentInitResult;
use App\Services\Payments\DTO\PaymentStatus;
use App\Services\Payments\DTO\WebhookResult;
use Illuminate\Http\Request;

/**
 * ManualGateway — cadangan. Pelanggan transfer manual; admin klik "Aktifkan"
 * di dashboard untuk mengonfirmasi. Tidak ada webhook.
 */
class ManualGateway implements PaymentGatewayInterface
{
    public function __construct(array $config = []) {}

    public function initiatePayment(Transaction $transaction): PaymentInitResult
    {
        return new PaymentInitResult(
            success: true,
            instructions: [
                'amount' => (int) $transaction->amount,
                'note'   => 'Transfer manual lalu tunggu konfirmasi admin THRE.F.NET.',
            ],
            message: 'Menunggu verifikasi manual oleh admin.',
        );
    }

    public function handleWebhook(Request $request): WebhookResult
    {
        // Tidak ada webhook untuk manual.
        return WebhookResult::invalid('Manual gateway tidak menerima webhook.');
    }

    public function checkStatus(string $orderId): PaymentStatus
    {
        $trx = Transaction::where('order_id', $orderId)->first();
        return new PaymentStatus($orderId, $trx?->status ?? 'pending');
    }

    public function getSupportedMethods(): array
    {
        return ['manual_transfer', 'cash', 'qris_static'];
    }

    public function getConfigFields(): array
    {
        return [
            'manual_bank_info' => ['label' => 'Info Rekening (ditampilkan ke pelanggan)', 'type' => 'textarea'],
        ];
    }
}

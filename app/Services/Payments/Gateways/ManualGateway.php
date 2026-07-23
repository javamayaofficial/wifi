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
    protected string $bankInfo;
    protected string $qrisImageUrl;
    protected string $qrisNote;
    protected string $cashNote;

    public function __construct(array $config = [])
    {
        $this->bankInfo = (string) ($config['bank_info'] ?? config('threfnet.payments.manual.bank_info', ''));
        $this->qrisImageUrl = (string) ($config['qris_image_url'] ?? config('threfnet.payments.manual.qris_image_url', ''));
        $this->qrisNote = (string) ($config['qris_note'] ?? config('threfnet.payments.manual.qris_note', ''));
        $this->cashNote = (string) ($config['cash_note'] ?? config('threfnet.payments.manual.cash_note', ''));
    }

    public function initiatePayment(Transaction $transaction): PaymentInitResult
    {
        $selectedMethod = (string) data_get($transaction->raw_response, 'selected_method', $transaction->payment_method);

        $instructions = [
            'amount' => (int) $transaction->amount,
        ];

        if ($selectedMethod === 'qris_static') {
            $instructions['qris_image_url'] = $this->qrisImageUrl;
            $instructions['note'] = $this->qrisNote ?: 'Scan QRIS lalu tunggu verifikasi admin THRE.F.NET.';
        } elseif ($selectedMethod === 'cash') {
            $instructions['note'] = $this->cashNote ?: 'Pembayaran tunai akan diverifikasi manual oleh admin THRE.F.NET.';
        } else {
            $instructions['bank_info'] = $this->bankInfo;
            $instructions['note'] = 'Transfer manual lalu tunggu konfirmasi admin THRE.F.NET.';
        }

        return new PaymentInitResult(
            success: true,
            instructions: $instructions,
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
            'manual_qris_image_url' => ['label' => 'URL Gambar QRIS Statis', 'type' => 'text'],
            'manual_qris_note' => ['label' => 'Catatan QRIS Statis', 'type' => 'textarea'],
            'manual_cash_note' => ['label' => 'Catatan Pembayaran Tunai', 'type' => 'textarea'],
        ];
    }
}

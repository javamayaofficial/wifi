<?php

namespace App\Services\Payments\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Transaction;
use App\Services\Payments\DTO\PaymentInitResult;
use App\Services\Payments\DTO\PaymentStatus;
use App\Services\Payments\DTO\WebhookResult;
use Illuminate\Http\Request;

/**
 * MootaGateway — pencocokan mutasi bank berbasis NOMINAL UNIK.
 * Moota tidak membuat halaman bayar; ia memantau mutasi & mengirim webhook.
 *
 * initiatePayment() -> menampilkan instruksi transfer nominal unik.
 * handleWebhook()    -> validasi HMAC signature + cocokkan nominal.
 */
class MootaGateway implements PaymentGatewayInterface
{
    protected string $secretToken;
    protected string $bankNumber;
    protected string $bankHolder;

    public function __construct(array $config = [])
    {
        $this->secretToken = $config['secret_token'] ?? (string) config('threfnet.moota.secret_token');
        $this->bankNumber  = $config['bank_number']  ?? (string) config('threfnet.moota.bank_number');
        $this->bankHolder  = $config['bank_holder']  ?? (string) config('threfnet.moota.bank_holder');
    }

    public function initiatePayment(Transaction $transaction): PaymentInitResult
    {
        // amount_final sudah = amount + unique_code (disiapkan saat transaksi dibuat).
        return new PaymentInitResult(
            success: true,
            instructions: [
                'bank_holder' => $this->bankHolder,
                'bank_number' => $this->bankNumber,
                'amount'      => (int) $transaction->amount_final,
                'note'        => 'Transfer TEPAT sampai 3 digit terakhir (kode unik) agar otomatis terverifikasi.',
            ],
            message: 'Silakan transfer sesuai nominal unik.',
        );
    }

    public function handleWebhook(Request $request): WebhookResult
    {
        $rawBody = $request->getContent();

        // Moota menandatangani payload dengan HMAC-SHA256 memakai secret token.
        $expected = hash_hmac('sha256', $rawBody, $this->secretToken);
        if (! hash_equals($expected, (string) $request->header('X-Signature'))) {
            return WebhookResult::invalid();
        }

        $payload = json_decode($rawBody, true) ?? [];

        // Moota mengirim array mutasi. Proses hanya kredit (dana masuk).
        $mutations = $payload['data'] ?? $payload;
        foreach ((is_array($mutations) ? $mutations : []) as $mutation) {
            if (strtoupper((string) ($mutation['type'] ?? '')) !== 'CR') {
                continue;
            }

            $amount = (int) round((float) ($mutation['amount'] ?? 0));

            $trx = Transaction::query()
                ->where('payment_method', 'moota')
                ->where('status', 'pending')
                ->where('amount_final', $amount)
                ->latest()
                ->first();

            if ($trx) {
                return new WebhookResult(
                    valid: true,
                    orderId: $trx->order_id,
                    status: 'paid',
                    raw: $mutation,
                );
            }
        }

        // Signature valid tapi belum ada match → biarkan pending (tidak error).
        return new WebhookResult(valid: true, status: 'pending', raw: $payload,
            message: 'Tidak ada transaksi cocok; ditandai untuk review.');
    }

    public function checkStatus(string $orderId): PaymentStatus
    {
        $trx = Transaction::where('order_id', $orderId)->first();
        return new PaymentStatus($orderId, $trx?->status ?? 'pending');
    }

    public function getSupportedMethods(): array
    {
        return ['bank_transfer'];
    }

    public function getConfigFields(): array
    {
        return [
            'moota_secret_token'    => ['label' => 'Moota Secret Token', 'type' => 'password'],
            'moota_bank_account_id' => ['label' => 'Moota Bank Account ID', 'type' => 'text'],
            'moota_bank_number'     => ['label' => 'Nomor Rekening', 'type' => 'text'],
            'moota_bank_holder'     => ['label' => 'Nama Pemilik Rekening', 'type' => 'text'],
        ];
    }
}

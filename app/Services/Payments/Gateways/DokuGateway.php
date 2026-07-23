<?php

namespace App\Services\Payments\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Transaction;
use App\Services\Payments\DTO\PaymentInitResult;
use App\Services\Payments\DTO\PaymentStatus;
use App\Services\Payments\DTO\WebhookResult;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * DokuGateway — dibangun langsung di atas GuzzleHttp mengikuti skema
 * signature resmi DOKU (HMAC-SHA256), di balik PaymentGatewayInterface.
 *
 * CATATAN: endpoint & nama field HARUS diverifikasi ke dokumentasi DOKU
 * (DOKU Back Office) sebelum produksi. Skema signature sudah sesuai standar.
 */
class DokuGateway implements PaymentGatewayInterface
{
    protected string $clientId;
    protected string $secretKey;
    protected string $baseUrl;

    public function __construct(array $config = [])
    {
        $this->clientId  = $config['client_id']  ?? (string) config('threfnet.doku.client_id');
        $this->secretKey = $config['secret_key'] ?? (string) config('threfnet.doku.secret_key');

        $env = $config['environment'] ?? config('threfnet.doku.environment', 'sandbox');
        $this->baseUrl = config("threfnet.doku.base_url.$env");
    }

    public function initiatePayment(Transaction $transaction): PaymentInitResult
    {
        $target    = '/checkout/v1/payment';
        $requestId = (string) Str::uuid();
        $timestamp = now()->utc()->format('Y-m-d\TH:i:s\Z');

        $body = [
            'order' => [
                'amount'          => (int) $transaction->amount,
                'invoice_number'  => $transaction->order_id,
                'currency'        => 'IDR',
                'callback_url'    => config('app.url') . '/payment/finish',
            ],
            'payment' => [
                'payment_due_date' => 60, // menit
            ],
            'customer' => [
                'name'  => $transaction->customer->name,
                'email' => $transaction->customer->email ?: 'noemail@thre.f.net',
            ],
        ];

        $jsonBody  = json_encode($body, JSON_UNESCAPED_SLASHES);
        $signature = $this->signRequest($target, $requestId, $timestamp, $jsonBody);

        try {
            $response = $this->http()->post($this->baseUrl . $target, [
                'headers' => [
                    'Client-Id'         => $this->clientId,
                    'Request-Id'        => $requestId,
                    'Request-Timestamp' => $timestamp,
                    'Signature'         => $signature,
                    'Content-Type'      => 'application/json',
                ],
                'body' => $jsonBody,
            ]);

            $data = json_decode((string) $response->getBody(), true);
            $url  = data_get($data, 'response.payment.url');

            if (! $url) {
                return PaymentInitResult::fail('DOKU tidak mengembalikan payment url.');
            }

            return new PaymentInitResult(
                success: true,
                redirectUrl: $url,
                message: 'Redirect ke halaman pembayaran DOKU.',
            );
        } catch (\Throwable $e) {
            report($e);
            return PaymentInitResult::fail('Gagal memanggil DOKU: ' . $e->getMessage());
        }
    }

    public function handleWebhook(Request $request): WebhookResult
    {
        $rawBody   = $request->getContent();
        $requestId = $request->header('Request-Id', '');
        $timestamp = $request->header('Request-Timestamp', '');
        $target    = '/api/payment/webhook/doku';

        $expected = $this->signRequest($target, $requestId, $timestamp, $rawBody);

        // Perbandingan konstan-waktu (anti timing attack)
        if (! hash_equals($expected, (string) $request->header('Signature'))) {
            return WebhookResult::invalid();
        }

        $data    = json_decode($rawBody, true) ?? [];
        $orderId = data_get($data, 'order.invoice_number') ?? data_get($data, 'transaction.original_request_id');
        $dokuStat = strtoupper((string) data_get($data, 'transaction.status', 'PENDING'));

        $status = match ($dokuStat) {
            'SUCCESS', 'PAID' => 'paid',
            'FAILED', 'EXPIRED' => 'failed',
            default => 'pending',
        };

        return new WebhookResult(
            valid: true,
            orderId: $orderId,
            status: $status,
            raw: $data,
        );
    }

    public function checkStatus(string $orderId): PaymentStatus
    {
        // Placeholder polling — implementasikan GET status DOKU sesuai kebutuhan.
        return new PaymentStatus(orderId: $orderId, status: 'pending');
    }

    public function getSupportedMethods(): array
    {
        return ['doku_qris', 'doku_va_bca', 'doku_va_bri', 'doku_va_mandiri', 'doku_va_bni', 'doku_credit_card'];
    }

    public function getConfigFields(): array
    {
        return [
            'doku_client_id'   => ['label' => 'DOKU Client ID', 'type' => 'text'],
            'doku_secret_key'  => ['label' => 'DOKU Secret Key', 'type' => 'password'],
            'doku_environment' => ['label' => 'Environment', 'type' => 'select', 'options' => ['sandbox', 'production']],
        ];
    }

    /** Bentuk signature "HMACSHA256=..." sesuai skema DOKU. */
    protected function signRequest(string $target, string $requestId, string $timestamp, string $body): string
    {
        $digest = base64_encode(hash('sha256', $body, true));

        $component = "Client-Id:{$this->clientId}\n"
            . "Request-Id:{$requestId}\n"
            . "Request-Timestamp:{$timestamp}\n"
            . "Request-Target:{$target}\n"
            . "Digest:{$digest}";

        return 'HMACSHA256=' . base64_encode(
            hash_hmac('sha256', $component, $this->secretKey, true)
        );
    }

    protected function http(): Client
    {
        return new Client(['timeout' => 15]);
    }
}

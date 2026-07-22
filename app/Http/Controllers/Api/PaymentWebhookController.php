<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\Payments\PaymentCompletionService;
use App\Services\Payments\PaymentManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Endpoint: POST /api/payment/webhook/{driver}
 * Alur: validasi signature -> cari transaksi -> PaymentCompletionService
 *        (idempotensi + perpanjang masa aktif + fire event) -> balas 200 cepat.
 */
class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PaymentManager $payments,
        protected PaymentCompletionService $completion,
    ) {}

    public function handle(Request $request, string $driver): JsonResponse
    {
        try {
            $gateway = $this->payments->driver($driver);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => 'Driver tidak dikenal.'], 404);
        }

        $result = $gateway->handleWebhook($request);

        // Signature tidak valid -> tolak (AC-4).
        if (! $result->valid) {
            Log::warning('THRE.F.NET webhook ditolak', [
                'driver' => $driver,
                'ip'     => $request->ip(),
                'msg'    => $result->message,
            ]);
            return response()->json(['message' => $result->message ?? 'Invalid signature'], 401);
        }

        // Belum lunas (mis. Moota belum ada match) -> akui 200 tanpa aksi.
        if ($result->status !== 'paid' || ! $result->orderId) {
            return response()->json(['message' => 'diterima'], 200);
        }

        $trx = Transaction::where('order_id', $result->orderId)->first();

        if (! $trx) {
            Log::warning('THRE.F.NET webhook: order tidak ditemukan', ['order_id' => $result->orderId]);
            return response()->json(['message' => 'order tidak ditemukan'], 200);
        }

        // Idempotensi ditangani di dalam service (AC-6).
        $this->completion->complete($trx, $result->raw);

        // Balas cepat; aktivasi MikroTik & notifikasi berjalan di queue.
        return response()->json(['message' => 'ok'], 200);
    }
}

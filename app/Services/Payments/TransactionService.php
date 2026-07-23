<?php

namespace App\Services\Payments;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Support\Str;

class TransactionService
{
    public function __construct(protected PaymentManager $payments) {}

    /**
     * Buat transaksi pending untuk pelanggan sesuai driver aktif.
     * Untuk Moota, amount_final = amount + kode unik 3 digit (untuk pencocokan mutasi).
     */
    public function createFor(Customer $customer, ?string $driver = null, ?string $paymentMethod = null): Transaction
    {
        $driver ??= $this->payments->activeDriverName();
        $amount = (float) $customer->plan->price;

        $amountFinal = $driver === 'moota'
            ? $amount + $this->uniqueCodeFor($customer, $amount)
            : $amount;

        $selectedMethod = $paymentMethod ?? $driver;

        return Transaction::create([
            'order_id'       => $this->generateOrderId(),
            'customer_id'    => $customer->id,
            'amount'         => $amount,
            'amount_final'   => $amountFinal,
            'payment_method' => $selectedMethod,
            'status'         => 'pending',
            'raw_response'   => [
                'driver' => $driver,
                'selected_method' => $selectedMethod,
            ],
        ]);
    }

    protected function generateOrderId(): string
    {
        return 'THREFNET-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }

    /**
     * Cari kode unik 3 digit yang belum dipakai transaksi pending lain
     * dengan nominal dasar sama, agar tidak ada nominal kembar.
     */
    protected function uniqueCodeFor(Customer $customer, float $amount): int
    {
        $taken = Transaction::query()
            ->whereIn('payment_method', ['moota', 'moota_bank_transfer'])
            ->where('status', 'pending')
            ->where('amount', $amount)
            ->pluck('amount_final')
            ->map(fn ($v) => (int) round((float) $v - $amount))
            ->all();

        for ($i = 0; $i < 900; $i++) {
            $code = random_int(100, 999);
            if (! in_array($code, $taken, true)) {
                $customer->update(['unique_code' => $code]);
                return $code;
            }
        }

        // Semua kode terpakai (sangat jarang) -> pakai 0, admin verifikasi manual.
        return 0;
    }
}

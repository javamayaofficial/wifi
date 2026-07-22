<?php

namespace App\Services\Payments;

use App\Events\PaymentSuccessEvent;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya tempat logika "transaksi menjadi lunas".
 * Dipakai oleh PaymentWebhookController (DOKU/Moota) DAN aktivasi manual admin,
 * sehingga perilaku idempotensi & perpanjangan masa aktif selalu konsisten.
 */
class PaymentCompletionService
{
    /**
     * Tandai transaksi lunas, perpanjang masa aktif, picu aktivasi.
     * Mengembalikan false bila transaksi sudah pernah diproses (idempotensi).
     */
    public function complete(Transaction $transaction, array $raw = []): bool
    {
        return DB::transaction(function () use ($transaction, $raw) {
            /** @var Transaction $trx */
            $trx = Transaction::query()
                ->whereKey($transaction->getKey())
                ->lockForUpdate()
                ->first();

            if (! $trx || $trx->status === 'paid') {
                return false; // sudah diproses -> jangan ulangi
            }

            $customer = $trx->customer()->with('plan')->first();
            if (! $customer) {
                return false;
            }

            // Perpanjang dari tanggal terbesar antara hari ini dan expired lama,
            // supaya pelanggan yang bayar lebih awal tidak kehilangan sisa hari.
            $base = $customer->expired_date && $customer->expired_date->isFuture()
                ? $customer->expired_date->copy()
                : now();

            $customer->expired_date = $base->addDays($customer->plan->duration_days);
            $customer->save();

            $trx->markPaid($raw);

            event(new PaymentSuccessEvent($customer, $trx));

            return true;
        });
    }
}

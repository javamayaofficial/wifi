<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use App\Services\Payments\PaymentManager;
use App\Services\Payments\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Halaman pembayaran untuk Pelanggan THRE.F.NET.
 *
 * CATATAN KEAMANAN: MVP mengidentifikasi pelanggan lewat {username}.
 * Halaman ini hanya menampilkan tagihan & memulai pembayaran (tidak mengubah
 * data sensitif). Untuk produksi, disarankan memakai signed URL Laravel
 * (URL::signedRoute) atau token per-pelanggan agar tagihan tidak bisa ditebak.
 */
class PaymentController extends Controller
{
    public function __construct(
        protected PaymentManager $payments,
        protected TransactionService $transactions,
    ) {}

    /** Tampilkan tagihan & tombol bayar. */
    public function show(string $username): View
    {
        $customer = Customer::with('plan')->where('username', $username)->firstOrFail();

        $driver  = $this->payments->activeDriverName();
        $methods = $this->payments->driver()->getSupportedMethods();

        $pending = Transaction::where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        return view('pay.show', compact('customer', 'driver', 'methods', 'pending'));
    }

    /** Buat transaksi & jalankan driver aktif. */
    public function pay(string $username): RedirectResponse
    {
        $customer = Customer::with('plan')->where('username', $username)->firstOrFail();

        // Gunakan transaksi pending yang ada agar tidak menumpuk tagihan ganda.
        $transaction = Transaction::where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->where('payment_method', $this->payments->activeDriverName())
            ->latest()
            ->first()
            ?? $this->transactions->createFor($customer);

        $result = $this->payments->driver()->initiatePayment($transaction);

        if (! $result->success) {
            return back()->with('error', $result->message ?? 'Gagal memulai pembayaran.');
        }

        // DOKU -> redirect ke halaman pembayaran eksternal.
        if ($result->redirectUrl) {
            return redirect()->away($result->redirectUrl);
        }

        // Moota / Manual -> tampilkan instruksi transfer.
        return redirect("/bayar/{$username}/instruksi/{$transaction->order_id}");
    }

    /** Instruksi transfer (Moota/Manual). */
    public function instructions(string $username, string $orderId): View
    {
        $transaction = Transaction::with('customer.plan')
            ->where('order_id', $orderId)
            ->firstOrFail();

        abort_unless($transaction->customer->username === $username, 404);

        $result = $this->payments->driver($transaction->payment_method)->initiatePayment($transaction);

        return view('pay.instructions', compact('transaction', 'result'));
    }

    /** Cek status pembayaran. */
    public function status(string $username, string $orderId): View
    {
        $transaction = Transaction::with('customer.plan')
            ->where('order_id', $orderId)
            ->firstOrFail();

        abort_unless($transaction->customer->username === $username, 404);

        return view('pay.status', compact('transaction'));
    }
}

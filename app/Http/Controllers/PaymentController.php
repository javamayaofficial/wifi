<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use App\Services\Payments\PaymentManager;
use App\Services\Payments\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        $options = $this->payments->publicOptions();
        $recommendedOption = collect($options)
            ->first(fn ($option) => $option['driver'] === $this->payments->activeDriverName() && ($option['ready'] ?? false));

        if (! $recommendedOption) {
            $recommendedOption = collect($options)->firstWhere('ready', true) ?? collect($options)->first();
        }

        $pending = Transaction::where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        return view('pay.show', compact('customer', 'options', 'pending', 'recommendedOption'));
    }

    /** Buat transaksi & jalankan metode yang dipilih pelanggan. */
    public function pay(Request $request, string $username): RedirectResponse
    {
        $customer = Customer::with('plan')->where('username', $username)->firstOrFail();
        $options = collect($this->payments->publicOptions())->keyBy('code');

        $data = $request->validate([
            'option' => ['required', 'string', Rule::in($options->keys()->all())],
        ]);

        $selected = $options->get($data['option']);

        if (! $selected || ! ($selected['ready'] ?? false)) {
            return back()->with('error', 'Metode pembayaran ini belum siap digunakan. Lengkapi pengaturannya dulu dari admin panel.');
        }

        $transaction = Transaction::where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->where('payment_method', $selected['code'])
            ->latest()
            ->first()
            ?? $this->transactions->createFor($customer, $selected['driver'], $selected['code']);

        $result = $this->payments->driver($selected['driver'])->initiatePayment($transaction);

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

        $driver = $this->payments->resolveDriverForTransaction($transaction);
        $paymentLabel = $this->payments->labelForMethod($transaction->payment_method);
        $result = $this->payments->driver($driver)->initiatePayment($transaction);

        return view('pay.instructions', compact('transaction', 'result', 'paymentLabel'));
    }

    /** Cek status pembayaran. */
    public function status(string $username, string $orderId): View
    {
        $transaction = Transaction::with('customer.plan')
            ->where('order_id', $orderId)
            ->firstOrFail();

        abort_unless($transaction->customer->username === $username, 404);

        $paymentLabel = $this->payments->labelForMethod($transaction->payment_method);

        return view('pay.status', compact('transaction', 'paymentLabel'));
    }
}

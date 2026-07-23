<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\InvoiceService;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function __construct(protected InvoiceService $invoices) {}

    /** Unduh dari dashboard admin. */
    public function download(Transaction $transaction): Response
    {
        return $this->invoices->render($transaction)
            ->download($this->invoices->filename($transaction));
    }

    /** Unduh dari halaman pembayaran pelanggan (dicocokkan dengan username). */
    public function publicDownload(string $username, string $orderId): Response
    {
        $transaction = Transaction::with('customer.plan')
            ->where('order_id', $orderId)
            ->firstOrFail();

        abort_unless($transaction->customer->username === $username, 404);

        return $this->invoices->render($transaction)
            ->download($this->invoices->filename($transaction));
    }
}

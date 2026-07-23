<?php

namespace App\Services;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Membuat invoice (belum dibayar) & kwitansi (sudah dibayar) dalam PDF.
 * Dokumen yang sama, hanya berbeda judul dan penanda lunas.
 */
class InvoiceService
{
    public function render(Transaction $transaction)
    {
        $transaction->loadMissing('customer.plan');

        return Pdf::loadView('invoices.document', [
            'trx'      => $transaction,
            'customer' => $transaction->customer,
            'isPaid'   => $transaction->status === 'paid',
            'company'  => [
                'name'    => config('app.name', 'THRE.F.NET'),
                'address' => \App\Models\Setting::get('company_address', ''),
                'phone'   => \App\Models\Setting::get('company_phone', ''),
                'email'   => \App\Models\Setting::get('company_email', 'info@thre.f.net'),
            ],
        ])->setPaper('a4');
    }

    public function filename(Transaction $transaction): string
    {
        $prefix = $transaction->status === 'paid' ? 'Kwitansi' : 'Invoice';

        return "{$prefix}-{$transaction->order_id}.pdf";
    }
}

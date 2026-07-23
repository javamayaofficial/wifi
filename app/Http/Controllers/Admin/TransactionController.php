<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\Payments\PaymentCompletionService;
use App\Services\Payments\PaymentManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        protected PaymentCompletionService $completion,
        protected PaymentManager $payments,
    ) {}

    public function index(Request $request): View
    {
        $transactions = Transaction::query()
            ->with('customer')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->method, fn ($q, $s) => $q->where('payment_method', $s))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $methodOptions = array_merge([
            'manual' => ['label' => 'Manual (Legacy)'],
            'moota' => ['label' => 'Moota (Legacy)'],
            'doku' => ['label' => 'DOKU (Legacy)'],
        ], $this->payments->optionCatalog());

        return view('transactions.index', compact('transactions', 'methodOptions'));
    }

    /**
     * AC-5: Admin verifikasi manual -> klik "Aktifkan" -> internet langsung aktif.
     * Memakai service yang sama dengan webhook, jadi idempotensi tetap terjaga.
     */
    public function activate(Transaction $transaction): RedirectResponse
    {
        $done = $this->completion->complete($transaction, [
            'manual_confirmation' => true,
            'confirmed_by'        => Auth::user()?->email ?? 'admin',
            'confirmed_at'        => now()->toIso8601String(),
        ]);

        return back()->with(
            $done ? 'success' : 'error',
            $done
                ? "Transaksi {$transaction->order_id} dikonfirmasi. Aktivasi sedang diproses."
                : "Transaksi {$transaction->order_id} sudah pernah diproses."
        );
    }
}

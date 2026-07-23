<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reseller;
use App\Models\ResellerTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ResellerController extends Controller
{
    public function index(): View
    {
        $resellers = Reseller::withCount('customers')->orderBy('name')->get();

        return view('resellers.index', compact('resellers'));
    }

    public function store(Request $request): RedirectResponse
    {
        Reseller::create($this->validated($request));

        return back()->with('success', 'Mitra ditambahkan.');
    }

    public function show(Reseller $reseller): View
    {
        $reseller->load(['customers.plan', 'transactions']);

        return view('resellers.show', compact('reseller'));
    }

    public function update(Request $request, Reseller $reseller): RedirectResponse
    {
        $reseller->update($this->validated($request));

        return back()->with('success', 'Data mitra diperbarui.');
    }

    /**
     * Catat mutasi saldo mitra. Saldo dihitung ulang di dalam transaksi
     * database + lock, supaya dua input bersamaan tidak menghasilkan saldo salah.
     */
    public function addTransaction(Request $request, Reseller $reseller): RedirectResponse
    {
        $data = $request->validate([
            'type'        => ['required', 'in:deposit,komisi,penarikan,penyesuaian'],
            'amount'      => ['required', 'numeric'],
            'description' => ['nullable', 'string', 'max:200'],
        ]);

        DB::transaction(function () use ($reseller, $data) {
            /** @var Reseller $locked */
            $locked = Reseller::whereKey($reseller->id)->lockForUpdate()->first();

            // Penarikan selalu mengurangi saldo.
            $amount = $data['type'] === 'penarikan'
                ? -abs((float) $data['amount'])
                : (float) $data['amount'];

            $balance = (float) $locked->deposit_balance + $amount;

            $locked->update(['deposit_balance' => $balance]);

            ResellerTransaction::create([
                'reseller_id'   => $locked->id,
                'type'          => $data['type'],
                'amount'        => $amount,
                'balance_after' => $balance,
                'description'   => $data['description'],
                'created_by'    => Auth::id(),
            ]);
        });

        return back()->with('success', 'Mutasi saldo dicatat.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name'               => ['required', 'string', 'max:120'],
            'phone'              => ['nullable', 'string', 'max:20'],
            'email'              => ['nullable', 'email', 'max:150'],
            'area'               => ['nullable', 'string', 'max:100'],
            'commission_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active'          => ['nullable', 'boolean'],
        ]);
    }
}

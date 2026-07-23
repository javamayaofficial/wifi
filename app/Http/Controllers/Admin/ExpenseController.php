<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Transaction;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $bulan = $request->bulan ?: now()->format('Y-m');
        [$tahun, $bln] = explode('-', $bulan);

        $expenses = Expense::query()
            ->whereYear('date', $tahun)
            ->whereMonth('date', $bln)
            ->when($request->category, fn ($q, $s) => $q->where('category', $s))
            ->orderByDesc('date')
            ->get();

        $perKategori = $expenses->groupBy('category')
            ->map(fn ($g) => $g->sum('amount'))
            ->sortDesc();

        return view('expenses.index', compact('expenses', 'perKategori', 'bulan'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date'        => ['required', 'date'],
            'category'    => ['required', 'in:' . implode(',', array_keys(Expense::CATEGORIES))],
            'description' => ['required', 'string', 'max:200'],
            'amount'      => ['required', 'numeric', 'min:0'],
            'attachment'  => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('expenses', 'public');
        }

        $data['created_by'] = Auth::id();

        Expense::create($data);

        return back()->with('success', 'Pengeluaran dicatat.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return back()->with('success', 'Pengeluaran dihapus.');
    }

    /** Laporan laba rugi sederhana: pemasukan vs pengeluaran per bulan. */
    public function profitLoss(Request $request): View
    {
        $tahun = (int) ($request->tahun ?: now()->year);

        $rows = [];
        $totalIn = $totalOut = 0;

        for ($m = 1; $m <= 12; $m++) {
            // Pemasukan = tagihan PPPoE yang lunas + penjualan voucher.
            $inBilling = (float) Transaction::where('status', 'paid')
                ->whereYear('paid_at', $tahun)
                ->whereMonth('paid_at', $m)
                ->sum('amount');

            $inVoucher = (float) Voucher::sold()
                ->whereYear('sold_at', $tahun)
                ->whereMonth('sold_at', $m)
                ->sum('sale_price');

            $in = $inBilling + $inVoucher;

            $out = (float) Expense::whereYear('date', $tahun)
                ->whereMonth('date', $m)
                ->sum('amount');

            $rows[] = [
                'bulan'   => \Carbon\Carbon::create($tahun, $m, 1)->translatedFormat('F'),
                'billing' => $inBilling,
                'voucher' => $inVoucher,
                'masuk'   => $in,
                'keluar'  => $out,
                'laba'    => $in - $out,
            ];

            $totalIn  += $in;
            $totalOut += $out;
        }

        $perKategori = Expense::whereYear('date', $tahun)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->sortDesc();

        return view('expenses.profit_loss', compact('rows', 'tahun', 'totalIn', 'totalOut', 'perKategori'));
    }
}

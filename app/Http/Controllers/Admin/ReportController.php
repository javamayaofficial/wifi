<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Voucher;
use App\Models\VoucherProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Laporan tunggakan (aging) — melihat siapa yang BELUM bayar,
     * berbeda dari laporan transaksi yang hanya mencatat yang sudah masuk.
     */
    public function arrears(): View
    {
        $customers = Customer::query()
            ->with('plan')
            ->whereIn('status', ['active', 'isolated', 'new'])
            ->whereDate('expired_date', '<', today())
            ->orderBy('expired_date')
            ->get();

        $buckets = [
            '1-7 hari'    => ['min' => 1,  'max' => 7,     'items' => collect(), 'total' => 0],
            '8-30 hari'   => ['min' => 8,  'max' => 30,    'items' => collect(), 'total' => 0],
            '31-60 hari'  => ['min' => 31, 'max' => 60,    'items' => collect(), 'total' => 0],
            '> 60 hari'   => ['min' => 61, 'max' => 99999, 'items' => collect(), 'total' => 0],
        ];

        foreach ($customers as $customer) {
            $days = (int) $customer->expired_date->diffInDays(today());

            foreach ($buckets as $label => $bucket) {
                if ($days >= $bucket['min'] && $days <= $bucket['max']) {
                    $customer->overdue_days = $days;
                    $buckets[$label]['items'] = $buckets[$label]['items']->push($customer);
                    $buckets[$label]['total'] += (float) $customer->plan->price;
                    break;
                }
            }
        }

        $grandTotal = collect($buckets)->sum('total');

        return view('reports.arrears', compact('buckets', 'grandTotal', 'customers'));
    }

    /** Laporan penjualan voucher: omzet, per profil, per agen, dan sisa stok. */
    public function vouchers(Request $request): View
    {
        $bulan = $request->bulan ?: now()->format('Y-m');
        [$tahun, $bln] = explode('-', $bulan);

        $terjual = Voucher::sold()
            ->with(['profile', 'reseller'])
            ->whereYear('sold_at', $tahun)
            ->whereMonth('sold_at', $bln)
            ->get();

        $omzet = (float) $terjual->sum('sale_price');

        $perProfil = $terjual->groupBy(fn ($v) => $v->profile?->name ?? '-')
            ->map(fn ($g) => ['jumlah' => $g->count(), 'omzet' => (float) $g->sum('sale_price')])
            ->sortByDesc('omzet');

        $perAgen = $terjual->groupBy(fn ($v) => $v->reseller?->name ?? 'Jual Langsung')
            ->map(fn ($g) => ['jumlah' => $g->count(), 'omzet' => (float) $g->sum('sale_price')])
            ->sortByDesc('omzet');

        // Stok: tersedia di gudang vs yang sedang dititipkan ke agen.
        $stok = VoucherProfile::orderBy('name')->get()->map(fn ($p) => [
            'nama'    => $p->name,
            'gudang'  => Voucher::where('voucher_profile_id', $p->id)
                ->where('status', 'tersedia')->whereNull('reseller_id')->count(),
            'di_agen' => Voucher::where('voucher_profile_id', $p->id)
                ->where('status', 'tersedia')->whereNotNull('reseller_id')->count(),
        ]);

        // Sisa titipan per agen, untuk menagih setoran.
        $titipan = Voucher::where('status', 'tersedia')
            ->whereNotNull('reseller_id')
            ->with(['reseller', 'profile'])
            ->get()
            ->groupBy(fn ($v) => $v->reseller?->name ?? '-')
            ->map(fn ($g) => $g->groupBy(fn ($v) => $v->profile?->name ?? '-')->map->count());

        return view('reports.vouchers', compact(
            'bulan', 'omzet', 'terjual', 'perProfil', 'perAgen', 'stok', 'titipan'
        ));
    }
}

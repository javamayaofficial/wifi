<?php

namespace App\Services;

use App\Models\Reseller;
use App\Models\ResellerTransaction;
use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Penjualan voucher — dua model penjualan:
 *
 *  1. JUAL LANGSUNG  : voucher dijual sendiri, pendapatan diakui saat itu juga
 *                      dengan harga jual profil.
 *  2. TITIP KE AGEN  : voucher diserahkan ke warung/agen (belum jadi pendapatan),
 *                      pendapatan diakui saat agen menyetorkan hasil penjualan
 *                      dengan harga agen. Selisih harga = margin agen.
 *
 * Pemisahan ini penting supaya omzet tidak dihitung dua kali dan stok di agen
 * tetap terlacak.
 */
class VoucherSalesService
{
    /** Serahkan sejumlah voucher tersedia ke agen (titip, belum jadi omzet). */
    public function handOver(Reseller $reseller, int $profileId, int $count): int
    {
        return DB::transaction(function () use ($reseller, $profileId, $count) {
            $vouchers = Voucher::query()
                ->where('voucher_profile_id', $profileId)
                ->where('status', 'tersedia')
                ->whereNull('reseller_id')
                ->limit($count)
                ->lockForUpdate()
                ->get();

            foreach ($vouchers as $voucher) {
                $voucher->update([
                    'reseller_id'    => $reseller->id,
                    'handed_over_at' => now(),
                ]);
            }

            return $vouchers->count();
        });
    }

    /** Jual langsung satu voucher (harga jual profil). */
    public function sellDirect(Voucher $voucher): bool
    {
        if ($voucher->status !== 'tersedia') {
            return false;
        }

        $voucher->update([
            'status'     => 'terjual',
            'sale_price' => $voucher->profile->price,
            'sold_by'    => Auth::id(),
            'sold_at'    => now(),
        ]);

        return true;
    }

    /**
     * Agen menyetorkan hasil penjualan: tandai N voucher miliknya sebagai terjual
     * dengan harga agen, lalu catat komisi/margin ke buku saldo mitra.
     *
     * @return array{count:int, omzet:float, margin:float}
     */
    public function settleFromAgent(Reseller $reseller, int $profileId, int $count): array
    {
        return DB::transaction(function () use ($reseller, $profileId, $count) {
            $vouchers = Voucher::query()
                ->with('profile')
                ->where('reseller_id', $reseller->id)
                ->where('voucher_profile_id', $profileId)
                ->where('status', 'tersedia')
                ->limit($count)
                ->lockForUpdate()
                ->get();

            if ($vouchers->isEmpty()) {
                return ['count' => 0, 'omzet' => 0.0, 'margin' => 0.0];
            }

            $profile = $vouchers->first()->profile;

            // Harga agen: bila belum diisi, pakai harga jual dikurangi komisi mitra.
            $agentPrice = (float) $profile->agent_price > 0
                ? (float) $profile->agent_price
                : (float) $profile->price * (1 - ((float) $reseller->commission_percent / 100));

            foreach ($vouchers as $voucher) {
                $voucher->update([
                    'status'     => 'terjual',
                    'sale_price' => $agentPrice,
                    'sold_by'    => Auth::id(),
                    'sold_at'    => now(),
                ]);
            }

            $jumlah = $vouchers->count();
            $omzet  = $agentPrice * $jumlah;
            $margin = ((float) $profile->price - $agentPrice) * $jumlah;

            // Catat margin agen sebagai komisi di buku saldo mitra (jejak audit).
            if ($margin > 0) {
                $locked  = Reseller::whereKey($reseller->id)->lockForUpdate()->first();
                $balance = (float) $locked->deposit_balance + $margin;

                $locked->update(['deposit_balance' => $balance]);

                ResellerTransaction::create([
                    'reseller_id'   => $locked->id,
                    'type'          => 'komisi',
                    'amount'        => $margin,
                    'balance_after' => $balance,
                    'description'   => "Margin {$jumlah} voucher {$profile->name}",
                    'created_by'    => Auth::id(),
                ]);
            }

            return ['count' => $jumlah, 'omzet' => $omzet, 'margin' => $margin];
        });
    }

    /** Total omzet voucher pada rentang bulan tertentu. */
    public function revenueForMonth(int $year, int $month): float
    {
        return (float) Voucher::sold()
            ->whereYear('sold_at', $year)
            ->whereMonth('sold_at', $month)
            ->sum('sale_price');
    }
}

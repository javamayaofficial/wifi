<?php

namespace App\Console\Commands;

use App\Models\Voucher;
use App\Models\VoucherProfile;
use Illuminate\Console\Command;

/**
 * Tandai voucher yang terlalu lama tersimpan sebagai kadaluarsa,
 * berdasarkan shelf_life_days pada profilnya (0 = tidak pernah hangus).
 *
 * Tanpa ini, stok "tersedia" terus membengkak oleh voucher lama yang
 * sebenarnya sudah tidak layak dijual, dan laporan stok jadi menyesatkan.
 */
class ExpireVouchers extends Command
{
    protected $signature = 'threfnet:expire-vouchers';
    protected $description = 'THRE.F.NET: tandai voucher lama sebagai kadaluarsa';

    public function handle(): int
    {
        $total = 0;

        foreach (VoucherProfile::where('shelf_life_days', '>', 0)->get() as $profile) {
            $batas = now()->subDays($profile->shelf_life_days);

            $count = Voucher::where('voucher_profile_id', $profile->id)
                ->where('status', 'tersedia')
                ->where('created_at', '<', $batas)
                ->update(['status' => 'kadaluarsa']);

            if ($count) {
                $this->line("  {$profile->name}: {$count} voucher kadaluarsa");
                $total += $count;
            }
        }

        $this->info("Total voucher kadaluarsa: {$total}");

        return self::SUCCESS;
    }
}

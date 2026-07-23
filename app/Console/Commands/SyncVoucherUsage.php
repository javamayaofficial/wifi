<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Services\Mikrotik\HotspotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Sinkronkan status pemakaian voucher dari MikroTik.
 * Voucher yang sudah dipakai (uptime > 0) ditandai "terpakai".
 */
class SyncVoucherUsage extends Command
{
    protected $signature = 'threfnet:sync-vouchers';
    protected $description = 'THRE.F.NET: sinkronkan pemakaian voucher dari router';

    public function handle(): int
    {
        $total = 0;

        foreach (Router::where('is_up', true)->get() as $router) {
            try {
                $count = (new HotspotService($router))->syncUsage();
                $total += $count;

                $this->line("  {$router->name}: {$count} voucher ditandai terpakai");
            } catch (\Throwable $e) {
                Log::warning('THRE.F.NET: gagal sync voucher', [
                    'router' => $router->name,
                    'error'  => $e->getMessage(),
                ]);

                $this->error("  {$router->name}: {$e->getMessage()}");
            }
        }

        $this->info("Total: {$total}");

        return self::SUCCESS;
    }
}

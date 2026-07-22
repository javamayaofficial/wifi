<?php

namespace App\Console\Commands;

use App\Events\CustomerExpiredEvent;
use App\Models\Customer;
use App\Services\Mikrotik\MikrotikService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Dijalankan tiap menit oleh scheduler.
 * Cek pelanggan expired -> isolir di MikroTik -> ubah status -> kirim notifikasi.
 */
class CheckExpiredCustomers extends Command
{
    protected $signature = 'threfnet:check-expired';
    protected $description = 'THRE.F.NET: isolir pelanggan yang sudah jatuh tempo';

    public function handle(): int
    {
        $expired = Customer::query()
            ->with(['plan', 'router'])
            ->where('status', 'active')
            ->whereDate('expired_date', '<', now()->toDateString())
            ->get();

        if ($expired->isEmpty()) {
            return self::SUCCESS;
        }

        $this->info("Memproses {$expired->count()} pelanggan expired...");

        foreach ($expired as $customer) {
            try {
                MikrotikService::forCustomer($customer)->disableUser($customer);
                $customer->update(['status' => 'isolated']);

                // Kirim notifikasi "THRE.F.NET - Internet Terisolir" (queue).
                event(new CustomerExpiredEvent($customer));

                $this->line("  ✔ Isolir: {$customer->username}");
            } catch (\Throwable $e) {
                Log::error('THRE.F.NET: gagal isolir pelanggan', [
                    'customer_id' => $customer->id,
                    'error'       => $e->getMessage(),
                ]);
                $this->error("  x Gagal: {$customer->username} - {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}

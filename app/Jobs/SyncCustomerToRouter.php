<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Services\Mikrotik\MikrotikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Dorong data pelanggan dari dashboard ke MikroTik (arah SATU JALUR).
 * Dijalankan di queue agar router yang lambat/mati tidak menggagalkan
 * penyimpanan data di dashboard.
 */
class SyncCustomerToRouter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public Customer $customer) {}

    public function handle(): void
    {
        $customer = $this->customer->fresh(['plan', 'router']);

        if (! $customer) {
            return;
        }

        try {
            MikrotikService::forCustomer($customer)->upsertSecret($customer);

            $customer->forceFill([
                'synced_at'  => now(),
                'sync_error' => null,
            ])->saveQuietly();
        } catch (\Throwable $e) {
            Log::error('THRE.F.NET: gagal sync pelanggan ke router', [
                'customer_id' => $customer->id,
                'error'       => $e->getMessage(),
            ]);

            $customer->forceFill(['sync_error' => $e->getMessage()])->saveQuietly();

            throw $e; // biarkan queue retry
        }
    }
}

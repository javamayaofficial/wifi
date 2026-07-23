<?php

namespace App\Jobs;

use App\Models\Router;
use App\Services\Mikrotik\MikrotikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/** Hapus PPP secret dari router setelah pelanggan dihapus di dashboard. */
class DeleteSecretFromRouter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public int $routerId, public string $username) {}

    public function handle(): void
    {
        $router = Router::find($this->routerId);

        if (! $router) {
            return;
        }

        try {
            (new MikrotikService($router))->deleteSecret($this->username);
        } catch (\Throwable $e) {
            Log::error('THRE.F.NET: gagal hapus secret di router', [
                'username' => $this->username,
                'error'    => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}

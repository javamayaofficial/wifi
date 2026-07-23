<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\Router;
use App\Services\Mikrotik\RouterImportService;
use Illuminate\Console\Command;

class ImportFromRouter extends Command
{
    protected $signature = 'threfnet:import-router {router_id} {--plan= : ID paket default} {--days=30}';
    protected $description = 'THRE.F.NET: import pelanggan dari PPP secret di MikroTik';

    public function handle(RouterImportService $importer): int
    {
        $router = Router::find($this->argument('router_id'));

        if (! $router) {
            $this->error('Router tidak ditemukan.');

            return self::FAILURE;
        }

        $planId = $this->option('plan') ?: Plan::value('id');

        if (! $planId) {
            $this->error('Belum ada paket. Buat paket dulu di /plans.');

            return self::FAILURE;
        }

        $this->info("Membaca PPP secret dari {$router->name} ({$router->ip})...");

        $result = $importer->import($router, (int) $planId, (int) $this->option('days'));

        $this->info("Selesai: {$result['imported']} diimport, {$result['skipped']} dilewati (sudah ada).");

        foreach ($result['errors'] as $err) {
            $this->warn('  - ' . $err);
        }

        return self::SUCCESS;
    }
}

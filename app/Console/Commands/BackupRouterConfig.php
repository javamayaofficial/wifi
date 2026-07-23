<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Services\Notifications\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RouterOS\Client;
use RouterOS\Query;

/**
 * Menyimpan konfigurasi setiap router sebagai teks (hasil /export).
 * Berguna saat router harus diganti atau ter-reset: konfigurasi bisa
 * dipulihkan tanpa menyusun ulang dari nol.
 */
class BackupRouterConfig extends Command
{
    protected $signature = 'threfnet:backup-routers {--keep=10}';
    protected $description = 'THRE.F.NET: simpan konfigurasi semua MikroTik';

    public function handle(TelegramService $telegram): int
    {
        $dir = storage_path('app/backups/routers');
        File::ensureDirectoryExists($dir);

        $gagal = [];

        foreach (Router::all() as $router) {
            try {
                $client = new Client([
                    'host'     => $router->ip,
                    'user'     => $router->username,
                    'pass'     => $router->password,
                    'port'     => (int) $router->api_port,
                    'ssl'      => (bool) $router->use_tls,
                    'timeout'  => 30,
                    'attempts' => 1,
                ]);

                $rows = $client->query(new Query('/export'))->read();

                $content = collect($rows)
                    ->map(fn ($r) => is_array($r) ? implode("\n", $r) : (string) $r)
                    ->implode("\n");

                if (trim($content) === '') {
                    throw new \RuntimeException('Hasil export kosong.');
                }

                $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($router->name));
                $file = "{$dir}/{$slug}-" . now()->format('Y-m-d_His') . '.rsc';

                File::put($file, $content);

                $this->info("OK  : {$router->name} -> " . basename($file));

                $this->prune($dir, $slug, (int) $this->option('keep'));
            } catch (\Throwable $e) {
                $gagal[] = "{$router->name}: {$e->getMessage()}";
                $this->error("GAGAL: {$router->name} - {$e->getMessage()}");
            }
        }

        if ($gagal) {
            $telegram->send("⚠️ <b>BACKUP CONFIG ROUTER GAGAL</b>\n" . implode("\n", $gagal));
        }

        return $gagal ? self::FAILURE : self::SUCCESS;
    }

    protected function prune(string $dir, string $slug, int $keep): void
    {
        collect(File::files($dir))
            ->filter(fn ($f) => str_starts_with($f->getFilename(), $slug . '-'))
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->values()
            ->slice($keep)
            ->each(fn ($f) => File::delete($f->getPathname()));
    }
}

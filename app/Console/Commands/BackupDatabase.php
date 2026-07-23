<?php

namespace App\Console\Commands;

use App\Services\Notifications\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 * Backup database harian ke storage/app/backups.
 *
 * Sistem billing menyimpan seluruh nadi usaha: pelanggan, transaksi, tagihan.
 * Backup bukan fitur opsional. Simpan juga salinan di luar server
 * (rsync/cloud) karena backup di server yang sama ikut hilang bila server rusak.
 */
class BackupDatabase extends Command
{
    protected $signature = 'threfnet:backup {--keep=14 : jumlah file backup yang disimpan}';
    protected $description = 'THRE.F.NET: backup database ke storage/app/backups';

    public function handle(TelegramService $telegram): int
    {
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        $file = $dir . '/threfnet-' . now()->format('Y-m-d_His') . '.sql.gz';

        $command = sprintf(
            'mysqldump --single-transaction --quick --host=%s --port=%s --user=%s --password=%s %s | gzip > %s',
            escapeshellarg((string) config('database.connections.mysql.host')),
            escapeshellarg((string) config('database.connections.mysql.port')),
            escapeshellarg((string) config('database.connections.mysql.username')),
            escapeshellarg((string) config('database.connections.mysql.password')),
            escapeshellarg((string) config('database.connections.mysql.database')),
            escapeshellarg($file)
        );

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful() || ! File::exists($file) || File::size($file) < 100) {
            $error = trim($process->getErrorOutput()) ?: 'File backup kosong.';

            $this->error('Backup GAGAL: ' . $error);

            $telegram->send("⚠️ <b>BACKUP GAGAL</b>\n" . $error . "\nTHRE.F.NET Billing System");

            return self::FAILURE;
        }

        $size = round(File::size($file) / 1048576, 2);
        $this->info("Backup selesai: {$file} ({$size} MB)");

        $this->pruneOld($dir, (int) $this->option('keep'));

        return self::SUCCESS;
    }

    /** Hapus backup lama agar disk tidak penuh. */
    protected function pruneOld(string $dir, int $keep): void
    {
        $files = collect(File::files($dir))
            ->filter(fn ($f) => str_ends_with($f->getFilename(), '.sql.gz'))
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->values();

        $files->slice($keep)->each(function ($f) {
            File::delete($f->getPathname());
            $this->line('Dihapus (lama): ' . $f->getFilename());
        });
    }
}

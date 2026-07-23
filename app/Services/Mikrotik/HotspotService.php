<?php

namespace App\Services\Mikrotik;

use App\Models\Router as RouterModel;
use App\Models\Voucher;
use App\Models\VoucherProfile;
use Illuminate\Support\Str;
use RouterOS\Client;
use RouterOS\Query;

/**
 * Voucher hotspot: generate kode di dashboard lalu didorong ke MikroTik
 * sebagai hotspot user. Arah tetap satu jalur (dashboard -> router).
 */
class HotspotService
{
    protected Client $client;

    public function __construct(protected RouterModel $router)
    {
        $this->client = new Client([
            'host'     => $router->ip,
            'user'     => $router->username,
            'pass'     => $router->password,
            'port'     => (int) $router->api_port,
            'ssl'      => (bool) $router->use_tls,
            'timeout'  => 10,
            'attempts' => 1,
        ]);
    }

    /**
     * Buat sejumlah voucher & dorong ke router.
     *
     * @return array{batch:string, created:int, errors:array}
     */
    public function generate(VoucherProfile $profile, int $count): array
    {
        $batch  = 'B' . now()->format('ymdHis');
        $errors = [];
        $created = 0;

        for ($i = 0; $i < $count; $i++) {
            $code = $this->uniqueCode($profile->code_length);

            try {
                $this->client->query(
                    (new Query('/ip/hotspot/user/add'))
                        ->equal('name', $code)
                        ->equal('password', $code)     // kode = password, mudah untuk pelanggan
                        ->equal('profile', $profile->hotspot_profile)
                        ->equal('comment', "THRE.F.NET {$batch}")
                )->read();

                Voucher::create([
                    'voucher_profile_id' => $profile->id,
                    'router_id'          => $this->router->id,
                    'batch'              => $batch,
                    'code'               => $code,
                    'password'           => $code,
                    'status'             => 'tersedia',
                ]);

                $created++;
            } catch (\Throwable $e) {
                $errors[] = "{$code}: {$e->getMessage()}";
            }
        }

        return compact('batch', 'created', 'errors');
    }

    /** Hapus voucher dari router (mis. batch yang dibatalkan). */
    public function removeFromRouter(string $code): void
    {
        $rows = $this->client->query(
            (new Query('/ip/hotspot/user/print'))->where('name', $code)
        )->read();

        if (! empty($rows[0]['.id'])) {
            $this->client->query(
                (new Query('/ip/hotspot/user/remove'))->equal('.id', $rows[0]['.id'])
            )->read();
        }
    }

    /** Tandai voucher yang sudah dipakai berdasarkan data router. */
    public function syncUsage(): int
    {
        $rows = $this->client->query(new Query('/ip/hotspot/user/print'))->read();
        $updated = 0;

        foreach ($rows as $row) {
            $used = ! empty($row['uptime']) && $row['uptime'] !== '00:00:00';

            if (! $used) {
                continue;
            }

            $voucher = Voucher::where('code', $row['name'] ?? '')
                ->where('router_id', $this->router->id)
                ->whereIn('status', ['tersedia', 'terjual'])
                ->first();

            if ($voucher) {
                $voucher->update(['status' => 'terpakai', 'used_at' => now()]);
                $updated++;
            }
        }

        return $updated;
    }

    public function listProfiles(): array
    {
        return $this->client->query(new Query('/ip/hotspot/user/profile/print'))->read();
    }

    protected function uniqueCode(int $length): string
    {
        do {
            // Hindari karakter ambigu (0/O, 1/I) agar tidak salah ketik pelanggan.
            $code = strtolower(Str::password($length, letters: true, numbers: true, symbols: false));
            $code = str_replace(['0', 'o', '1', 'l', 'i'], ['2', 'p', '3', 'm', 'n'], $code);
        } while (Voucher::where('code', $code)->exists());

        return $code;
    }
}

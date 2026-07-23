<?php

namespace App\Services\Mikrotik;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;
use Illuminate\Support\Facades\DB;

/**
 * Import pelanggan dari PPP secret yang sudah ada di MikroTik.
 * Lebih akurat daripada Import Excel karena data diambil dari sumbernya.
 *
 * CATATAN: RouterOS API tidak mengembalikan password secret dalam bentuk
 * terbaca pada sebagian versi/konfigurasi. Bila password kosong, sistem
 * membuat password acak dan MENDORONGNYA ke router agar dashboard dan router
 * tetap sinkron (dashboard = sumber kebenaran).
 */
class RouterImportService
{
    /** Ambil daftar secret + tandai mana yang sudah/belum ada di dashboard. */
    public function preview(Router $router): array
    {
        $secrets = (new MikrotikService($router))->listSecrets();

        $existing = Customer::pluck('id', 'username');
        $planByProfile = Plan::pluck('id', 'mikrotik_profile');

        $rows = [];

        foreach ($secrets as $secret) {
            $username = $secret['name'] ?? null;

            if (! $username) {
                continue;
            }

            $profile = $secret['profile'] ?? 'default';

            $rows[] = [
                'username'    => $username,
                'profile'     => $profile,
                'comment'     => $secret['comment'] ?? '',
                'disabled'    => ($secret['disabled'] ?? 'false') === 'true',
                'has_password'=> ! blank($secret['password'] ?? null),
                'exists'      => isset($existing[$username]),
                'plan_id'     => $planByProfile[$profile] ?? null,
            ];
        }

        return $rows;
    }

    /**
     * Jalankan import. Hanya membuat pelanggan BARU; yang sudah ada dilewati
     * agar data billing di dashboard tidak tertimpa.
     *
     * @return array{imported:int, skipped:int, errors:array}
     */
    public function import(Router $router, int $defaultPlanId, int $defaultDurationDays = 30): array
    {
        $secrets = (new MikrotikService($router))->listSecrets();

        $existing = Customer::pluck('id', 'username');
        $planByProfile = Plan::pluck('id', 'mikrotik_profile');

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($secrets as $secret) {
            $username = $secret['name'] ?? null;

            if (! $username) {
                continue;
            }

            if (isset($existing[$username])) {
                $skipped++;
                continue;
            }

            $profile  = $secret['profile'] ?? 'default';
            $password = $secret['password'] ?? null;
            $generated = false;

            if (blank($password)) {
                $password  = 'thre' . random_int(100000, 999999);
                $generated = true;
            }

            try {
                DB::transaction(function () use (
                    $router, $username, $password, $profile,
                    $planByProfile, $defaultPlanId, $defaultDurationDays,
                    $secret, &$imported
                ) {
                    Customer::create([
                        'name'         => $secret['comment'] ?: $username,
                        'username'     => $username,
                        'password'     => $password,
                        'plan_id'      => $planByProfile[$profile] ?? $defaultPlanId,
                        'router_id'    => $router->id,
                        'expired_date' => now()->addDays($defaultDurationDays),
                        'status'       => ($secret['disabled'] ?? 'false') === 'true' ? 'isolated' : 'active',
                    ]);

                    $imported++;
                });

                if ($generated) {
                    $errors[] = "{$username}: password tidak terbaca dari router, "
                        . 'dibuatkan password baru dan didorong ulang ke router.';
                }
            } catch (\Throwable $e) {
                $errors[] = "{$username}: {$e->getMessage()}";
            }
        }

        return compact('imported', 'skipped', 'errors');
    }
}

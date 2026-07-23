<?php

namespace App\Services\Mikrotik;

use App\Models\Customer;
use App\Models\Router as RouterModel;
use RouterOS\Client;
use RouterOS\Query;

/**
 * MikrotikService — pembungkus evilfreelancer/routeros-api-php.
 *
 * PRINSIP: Dashboard adalah SUMBER KEBENARAN untuk data billing
 * (username, password, profile, aktif/isolir). Router adalah sumber kebenaran
 * untuk data runtime (sesi aktif, resource). Penulisan hanya satu arah:
 * dashboard -> router. Data runtime hanya dibaca, tidak pernah ditulis balik
 * ke tabel billing.
 */
class MikrotikService
{
    protected Client $client;

    public function __construct(protected RouterModel $router)
    {
        $this->client = new Client([
            'host'     => $router->ip,
            'user'     => $router->username,
            'pass'     => $router->password,   // didekripsi otomatis oleh cast
            'port'     => (int) $router->api_port,
            'ssl'      => (bool) $router->use_tls,
            'timeout'  => 5,
            'attempts' => 1,
        ]);
    }

    public static function forCustomer(Customer $customer): self
    {
        return new self($customer->router);
    }

    // =====================================================================
    // TULIS: dashboard -> router
    // =====================================================================

    /**
     * Buat ATAU perbarui PPP secret sesuai data pelanggan di dashboard.
     * Idempotent: aman dipanggil berulang.
     */
    public function upsertSecret(Customer $customer): void
    {
        $profile = $customer->status === 'active'
            ? $customer->plan->mikrotik_profile
            : config('threfnet.mikrotik.isolir_profile', 'isolir');

        $id = $this->findSecretId($customer->username);

        if ($id === null) {
            $this->client->query(
                (new Query('/ppp/secret/add'))
                    ->equal('name', $customer->username)
                    ->equal('password', $customer->password)
                    ->equal('service', 'pppoe')
                    ->equal('profile', $profile)
                    ->equal('comment', 'THRE.F.NET #' . $customer->id)
                    ->equal('disabled', 'no')
            )->read();

            return;
        }

        $this->client->query(
            (new Query('/ppp/secret/set'))
                ->equal('.id', $id)
                ->equal('password', $customer->password)
                ->equal('profile', $profile)
                ->equal('comment', 'THRE.F.NET #' . $customer->id)
        )->read();
    }

    /** Aktifkan pelanggan: pastikan secret ada, enable, set profile paket. */
    public function enableUser(Customer $customer): void
    {
        $id = $this->findSecretId($customer->username);

        if ($id === null) {
            // Secret belum ada di router -> buat sekalian (self-healing).
            $this->upsertSecret($customer);
            $id = $this->findSecretId($customer->username);
        }

        $this->client->query(
            (new Query('/ppp/secret/set'))
                ->equal('.id', $id)
                ->equal('disabled', 'no')
                ->equal('password', $customer->password)
                ->equal('profile', $customer->plan->mikrotik_profile)
        )->read();
    }

    /** Isolir pelanggan: pindah ke profile isolir + putus sesi aktif. */
    public function disableUser(Customer $customer): void
    {
        $id = $this->findSecretId($customer->username);

        if ($id === null) {
            return; // tidak ada di router, tidak ada yang perlu diisolir
        }

        $this->client->query(
            (new Query('/ppp/secret/set'))
                ->equal('.id', $id)
                ->equal('profile', config('threfnet.mikrotik.isolir_profile', 'isolir'))
        )->read();

        $this->killActiveSession($customer->username);
    }

    /** Hapus secret dari router (dipakai saat pelanggan dihapus). */
    public function deleteSecret(string $username): void
    {
        $id = $this->findSecretId($username);

        if ($id === null) {
            return;
        }

        $this->killActiveSession($username);

        $this->client->query(
            (new Query('/ppp/secret/remove'))->equal('.id', $id)
        )->read();
    }

    /** Putus paksa sesi pelanggan (tombol "Putus" di monitoring). */
    public function killActiveSession(string $username): void
    {
        $active = $this->client->query(
            (new Query('/ppp/active/print'))->where('name', $username)
        )->read();

        if (! empty($active[0]['.id'])) {
            $this->client->query(
                (new Query('/ppp/active/remove'))->equal('.id', $active[0]['.id'])
            )->read();
        }
    }

    // =====================================================================
    // BACA: router -> dashboard (runtime, tidak pernah menimpa data billing)
    // =====================================================================

    /** Semua PPP secret di router (untuk fitur Import dari Router). */
    public function listSecrets(): array
    {
        return $this->client->query(new Query('/ppp/secret/print'))->read();
    }

    /** Sesi PPPoE yang sedang online. */
    public function listActive(): array
    {
        return $this->client->query(new Query('/ppp/active/print'))->read();
    }

    /** Daftar PPP profile (untuk dropdown paket, agar bebas typo). */
    public function listProfiles(): array
    {
        return $this->client->query(new Query('/ppp/profile/print'))->read();
    }

    /** CPU, RAM, uptime, versi RouterOS. */
    public function systemResource(): array
    {
        $rows = $this->client->query(new Query('/system/resource/print'))->read();

        return $rows[0] ?? [];
    }

    public function identity(): string
    {
        $rows = $this->client->query(new Query('/system/identity/print'))->read();

        return $rows[0]['name'] ?? $this->router->name;
    }

    public function testConnection(): bool
    {
        try {
            $this->client->query(new Query('/system/identity/print'))->read();

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    /** Kembalikan .id secret, atau null bila belum ada. */
    protected function findSecretId(string $username): ?string
    {
        $rows = $this->client->query(
            (new Query('/ppp/secret/print'))->where('name', $username)
        )->read();

        return $rows[0]['.id'] ?? null;
    }
}

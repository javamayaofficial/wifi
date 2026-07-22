<?php

namespace App\Services\Mikrotik;

use App\Models\Customer;
use App\Models\Router as RouterModel;
use RouterOS\Client;
use RouterOS\Query;

/**
 * MikrotikService — pembungkus evilfreelancer/routeros-api-php.
 * Model isolasi: pindah profile PPP secret ke "isolir" + putus sesi aktif,
 * bukan sekadar disable, agar pelanggan bisa diarahkan ke walled garden.
 */
class MikrotikService
{
    protected Client $client;

    public function __construct(protected RouterModel $router)
    {
        $this->client = new Client([
            'host'    => $router->ip,
            'user'    => $router->username,
            'pass'    => $router->password,   // otomatis didekripsi oleh cast
            'port'    => (int) $router->api_port,
            'ssl'     => (bool) $router->use_tls,
            'timeout' => 5,
            'attempts'=> 1,
        ]);
    }

    public static function forCustomer(Customer $customer): self
    {
        return new self($customer->router);
    }

    /** Aktifkan pelanggan: enable secret + set profile sesuai paket. */
    public function enableUser(Customer $customer): void
    {
        $id = $this->findSecretId($customer->username);

        // Pastikan kredensial PPPoE sinkron dengan database.
        $set = (new Query('/ppp/secret/set'))
            ->equal('.id', $id)
            ->equal('disabled', 'no')
            ->equal('password', $customer->password)
            ->equal('profile', $customer->plan->mikrotik_profile);

        $this->client->query($set)->read();
    }

    /** Isolir pelanggan: pindah ke profile isolir + putus sesi aktif. */
    public function disableUser(Customer $customer): void
    {
        $id = $this->findSecretId($customer->username);
        $isolir = config('threfnet.mikrotik.isolir_profile', 'isolir');

        $this->client->query(
            (new Query('/ppp/secret/set'))
                ->equal('.id', $id)
                ->equal('profile', $isolir)
        )->read();

        $this->killActiveSession($customer->username);
    }

    /** Untuk tombol "Test Koneksi" di dashboard. */
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

    protected function findSecretId(string $username): string
    {
        $rows = $this->client->query(
            (new Query('/ppp/secret/print'))->where('name', $username)
        )->read();

        if (empty($rows[0]['.id'])) {
            throw new \RuntimeException("PPP secret [{$username}] tidak ditemukan di router {$this->router->name}.");
        }

        return $rows[0]['.id'];
    }

    protected function killActiveSession(string $username): void
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
}

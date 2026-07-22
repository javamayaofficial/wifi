<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Setting;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

/**
 * PaymentManager — Factory yang membaca driver aktif dari tabel settings
 * (key: active_driver) lalu mengembalikan implementasi PaymentGatewayInterface.
 *
 * Menambah driver baru cukup: buat class implements PaymentGatewayInterface,
 * daftarkan binding di PaymentServiceProvider, dan tambah di peta $map.
 * Kode inti (controller/listener) TIDAK berubah.
 */
class PaymentManager
{
    protected array $map = [
        'doku'   => 'gateway.doku',
        'moota'  => 'gateway.moota',
        'manual' => 'gateway.manual',
    ];

    public function __construct(protected Container $app) {}

    public function driver(?string $name = null): PaymentGatewayInterface
    {
        $name ??= Setting::get('active_driver', 'manual');

        if (! isset($this->map[$name])) {
            throw new InvalidArgumentException("Driver pembayaran [{$name}] tidak dikenal.");
        }

        return $this->app->make($this->map[$name]);
    }

    public function activeDriverName(): string
    {
        return Setting::get('active_driver', 'manual');
    }

    public function available(): array
    {
        return array_keys($this->map);
    }
}

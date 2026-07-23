<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Transaction;
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

    public function optionCatalog(): array
    {
        return [
            'qris_static' => [
                'driver' => 'manual',
                'label' => 'QRIS Statis',
                'description' => 'Pelanggan scan QRIS lalu admin verifikasi pembayaran.',
                'group' => 'Manual',
            ],
            'manual_transfer' => [
                'driver' => 'manual',
                'label' => 'Transfer Manual',
                'description' => 'Transfer ke rekening lalu diverifikasi manual oleh admin.',
                'group' => 'Manual',
            ],
            'cash' => [
                'driver' => 'manual',
                'label' => 'Bayar Tunai',
                'description' => 'Cocok untuk penagihan lapangan atau pembayaran di kantor.',
                'group' => 'Manual',
            ],
            'moota_bank_transfer' => [
                'driver' => 'moota',
                'label' => 'Transfer Bank Otomatis',
                'description' => 'Transfer ke rekening sendiri dengan pencocokan mutasi Moota.',
                'group' => 'Otomatis',
            ],
            'doku_qris' => [
                'driver' => 'doku',
                'label' => 'QRIS Otomatis',
                'description' => 'Pembayaran QRIS melalui gateway DOKU.',
                'group' => 'Otomatis',
            ],
            'doku_va_bca' => [
                'driver' => 'doku',
                'label' => 'VA BCA',
                'description' => 'Virtual account BCA via DOKU.',
                'group' => 'Otomatis',
            ],
            'doku_va_bri' => [
                'driver' => 'doku',
                'label' => 'VA BRI',
                'description' => 'Virtual account BRI via DOKU.',
                'group' => 'Otomatis',
            ],
            'doku_va_mandiri' => [
                'driver' => 'doku',
                'label' => 'VA Mandiri',
                'description' => 'Virtual account Mandiri via DOKU.',
                'group' => 'Otomatis',
            ],
            'doku_va_bni' => [
                'driver' => 'doku',
                'label' => 'VA BNI',
                'description' => 'Virtual account BNI via DOKU.',
                'group' => 'Otomatis',
            ],
            'doku_credit_card' => [
                'driver' => 'doku',
                'label' => 'Kartu Kredit',
                'description' => 'Pembayaran kartu kredit melalui gateway DOKU.',
                'group' => 'Otomatis',
            ],
        ];
    }

    public function publicOptions(): array
    {
        $options = [];

        foreach ($this->optionCatalog() as $code => $meta) {
            $enabled = $this->settingFlag('payment_option_' . $code, true);

            if (! $enabled) {
                continue;
            }

            $ready = $this->isOptionReady($code, $meta['driver']);

            $options[] = [
                'code' => $code,
                'driver' => $meta['driver'],
                'label' => $meta['label'],
                'description' => $meta['description'],
                'group' => $meta['group'],
                'enabled' => true,
                'ready' => $ready,
                'status_label' => $ready ? 'Siap dipakai' : 'Lengkapi konfigurasi',
            ];
        }

        return $options;
    }

    public function resolveDriverForMethod(string $method): string
    {
        if (isset($this->map[$method])) {
            return $method;
        }

        $catalog = $this->optionCatalog();

        if (! isset($catalog[$method])) {
            throw new InvalidArgumentException("Metode pembayaran [{$method}] tidak dikenal.");
        }

        return $catalog[$method]['driver'];
    }

    public function resolveDriverForTransaction(Transaction $transaction): string
    {
        $driver = data_get($transaction->raw_response, 'driver');

        if (is_string($driver) && isset($this->map[$driver])) {
            return $driver;
        }

        return $this->resolveDriverForMethod($transaction->payment_method);
    }

    public function labelForMethod(?string $method): string
    {
        if (! $method) {
            return '-';
        }

        if (isset($this->optionCatalog()[$method])) {
            return $this->optionCatalog()[$method]['label'];
        }

        return strtoupper($method);
    }

    protected function isOptionReady(string $code, string $driver): bool
    {
        return match ($code) {
            'qris_static' => filled(Setting::get('manual_qris_image_url', config('threfnet.payments.manual.qris_image_url'))),
            'manual_transfer' => filled(Setting::get('manual_bank_info', config('threfnet.payments.manual.bank_info'))),
            'cash' => true,
            'moota_bank_transfer' => filled(Setting::get('moota_secret_token', config('threfnet.moota.secret_token')))
                && filled(Setting::get('moota_bank_number', config('threfnet.moota.bank_number')))
                && filled(Setting::get('moota_bank_holder', config('threfnet.moota.bank_holder'))),
            default => $driver !== 'doku'
                || (filled(Setting::get('doku_client_id', config('threfnet.doku.client_id')))
                    && filled(Setting::get('doku_secret_key', config('threfnet.doku.secret_key')))),
        };
    }

    protected function settingFlag(string $key, bool $default = false): bool
    {
        return filter_var(Setting::get($key, $default ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    }
}

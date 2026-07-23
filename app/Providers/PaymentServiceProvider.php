<?php

namespace App\Providers;

use App\Contracts\NotificationServiceInterface;
use App\Services\NotificationManager;
use App\Services\Payments\Gateways\DokuGateway;
use App\Services\Payments\Gateways\ManualGateway;
use App\Services\Payments\Gateways\MootaGateway;
use App\Services\Payments\PaymentManager;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // --- Payment drivers ---
        $this->app->bind('gateway.doku', fn () => new DokuGateway([
            'client_id'   => \App\Models\Setting::get('doku_client_id', config('threfnet.doku.client_id')),
            'secret_key'  => \App\Models\Setting::get('doku_secret_key', config('threfnet.doku.secret_key')),
            'environment' => \App\Models\Setting::get('doku_environment', config('threfnet.doku.environment')),
        ]));

        $this->app->bind('gateway.moota', fn () => new MootaGateway([
            'secret_token' => \App\Models\Setting::get('moota_secret_token', config('threfnet.moota.secret_token')),
            'bank_number'  => \App\Models\Setting::get('moota_bank_number', config('threfnet.moota.bank_number')),
            'bank_holder'  => \App\Models\Setting::get('moota_bank_holder', config('threfnet.moota.bank_holder')),
        ]));

        $this->app->bind('gateway.manual', fn () => new ManualGateway([
            'bank_info' => \App\Models\Setting::get('manual_bank_info', config('threfnet.payments.manual.bank_info')),
            'qris_image_url' => \App\Models\Setting::get('manual_qris_image_url', config('threfnet.payments.manual.qris_image_url')),
            'qris_note' => \App\Models\Setting::get('manual_qris_note', config('threfnet.payments.manual.qris_note')),
            'cash_note' => \App\Models\Setting::get('manual_cash_note', config('threfnet.payments.manual.cash_note')),
        ]));

        // --- Payment manager (factory) ---
        $this->app->singleton(PaymentManager::class, fn ($app) => new PaymentManager($app));

        // --- Notification manager terikat ke kontrak ---
        $this->app->singleton(NotificationServiceInterface::class, NotificationManager::class);
    }

    public function boot(): void
    {
        //
    }
}

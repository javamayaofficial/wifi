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

        $this->app->bind('gateway.manual', fn () => new ManualGateway());

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

<?php

namespace App\Providers;

use App\Models\Customer;
use App\Observers\CustomerObserver;
use Illuminate\Support\ServiceProvider;

class MikrotikServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Dashboard = sumber kebenaran: setiap perubahan pelanggan
        // otomatis didorong ke MikroTik lewat queue.
        Customer::observe(CustomerObserver::class);
    }
}

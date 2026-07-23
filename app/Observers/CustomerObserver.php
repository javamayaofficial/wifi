<?php

namespace App\Observers;

use App\Jobs\DeleteSecretFromRouter;
use App\Jobs\SyncCustomerToRouter;
use App\Models\Customer;

/**
 * Menjaga MikroTik selalu mengikuti dashboard (dashboard = sumber kebenaran).
 * Semua dorongan berjalan lewat queue agar UI tetap responsif.
 */
class CustomerObserver
{
    /** Field yang bila berubah wajib didorong ulang ke router. */
    protected array $watched = ['username', 'password', 'plan_id', 'router_id', 'status'];

    public function created(Customer $customer): void
    {
        SyncCustomerToRouter::dispatch($customer);
    }

    public function updated(Customer $customer): void
    {
        if (! $customer->wasChanged($this->watched)) {
            return;
        }

        // Bila pelanggan dipindah router, hapus dulu secret di router lama.
        if ($customer->wasChanged('router_id')) {
            $oldRouterId = $customer->getOriginal('router_id');

            if ($oldRouterId) {
                DeleteSecretFromRouter::dispatch((int) $oldRouterId, $customer->username);
            }
        }

        // Bila username diganti, hapus secret lama agar tidak jadi yatim.
        if ($customer->wasChanged('username')) {
            $oldUsername = $customer->getOriginal('username');

            if ($oldUsername) {
                DeleteSecretFromRouter::dispatch((int) $customer->router_id, $oldUsername);
            }
        }

        SyncCustomerToRouter::dispatch($customer);
    }

    public function deleted(Customer $customer): void
    {
        DeleteSecretFromRouter::dispatch((int) $customer->router_id, $customer->username);
    }
}

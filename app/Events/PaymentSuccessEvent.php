<?php

namespace App\Events;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Customer $customer,
        public Transaction $transaction,
    ) {}
}

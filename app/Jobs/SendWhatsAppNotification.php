<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Services\NotificationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public Customer $customer,
        public string $message,
        public ?string $context = null,
    ) {}

    public function handle(NotificationManager $notifications): void
    {
        if (! $this->customer->phone) {
            return;
        }
        $notifications->sendWhatsApp($this->customer->phone, $this->message, $this->customer, $this->context);
    }
}

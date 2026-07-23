<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Services\NotificationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmailNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public Customer $customer,
        public string $subject,
        public string $htmlContent,
        public ?string $context = null,
    ) {}

    public function handle(NotificationManager $notifications): void
    {
        if (! $this->customer->email) {
            return;
        }
        $notifications->sendEmail($this->customer->email, $this->subject, $this->htmlContent, $this->customer, $this->context);
    }
}

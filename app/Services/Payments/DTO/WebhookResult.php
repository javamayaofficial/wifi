<?php

namespace App\Services\Payments\DTO;

class WebhookResult
{
    public function __construct(
        public bool $valid,              // signature valid?
        public ?string $orderId = null,
        public string $status = 'pending', // paid | pending | failed
        public array $raw = [],
        public ?string $message = null,
    ) {}

    public static function invalid(string $message = 'Signature tidak valid'): self
    {
        return new self(valid: false, message: $message);
    }

    public function isPaid(): bool
    {
        return $this->valid && $this->status === 'paid';
    }
}

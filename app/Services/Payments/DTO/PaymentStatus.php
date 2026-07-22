<?php

namespace App\Services\Payments\DTO;

class PaymentStatus
{
    public function __construct(
        public string $orderId,
        public string $status,   // pending | paid | expired | failed
        public array $raw = [],
    ) {}
}

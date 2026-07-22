<?php

namespace App\Services\Payments\DTO;

class PaymentInitResult
{
    public function __construct(
        public bool $success,
        public ?string $redirectUrl = null,   // DOKU: halaman bayar
        public ?string $vaNumber = null,
        public ?string $qrString = null,
        public ?array $instructions = null,   // Moota/Manual: instruksi transfer
        public ?string $message = null,
    ) {}

    public static function fail(string $message): self
    {
        return new self(success: false, message: $message);
    }
}

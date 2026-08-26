<?php

namespace App\Services\Payments;

class PaymentIntent
{
    public function __construct(
        public ?string $redirectUrl = null,
        public array $instructions = [],
        public ?string $reference = null,
    ) {}

    public function requiresRedirect(): bool
    {
        return ! empty($this->redirectUrl);
    }
}

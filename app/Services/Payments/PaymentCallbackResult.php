<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;

class PaymentCallbackResult
{
    public function __construct(
        public bool $verified,
        public ?string $reference = null,
        public PaymentStatus $status = PaymentStatus::Gagal,
        public array $payload = [],
        public ?string $message = null,
        public ?float $amount = null,
    ) {}
}

<?php

namespace App\Contracts;

interface PaymentGateway
{
    /** @return array<string, mixed> */
    public function createIntent(int $amountPaise, string $reference, array $metadata = []): array;
}

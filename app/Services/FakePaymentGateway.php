<?php

namespace App\Services;

use App\Contracts\PaymentGateway;

class FakePaymentGateway implements PaymentGateway
{
    public function createIntent(int $amountPaise, string $reference, array $metadata = []): array
    {
        return ['provider' => 'fake', 'status' => 'created', 'provider_reference' => 'fake_'.$reference, 'amount_paise' => $amountPaise, 'metadata' => $metadata];
    }
}

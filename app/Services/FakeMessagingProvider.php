<?php

namespace App\Services;

use App\Contracts\MessagingProvider;

class FakeMessagingProvider implements MessagingProvider
{
    public function send(string $channel, string $recipient, string $template, array $payload = []): array
    {
        return ['provider' => 'fake', 'status' => 'simulated', 'channel' => $channel, 'recipient' => $recipient, 'template' => $template, 'payload' => $payload];
    }
}

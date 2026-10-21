<?php

namespace App\Contracts;

interface MessagingProvider
{
    /** @param array<string, mixed> $payload */
    public function send(string $channel, string $recipient, string $template, array $payload = []): array;
}

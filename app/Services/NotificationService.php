<?php

namespace App\Services;

use App\Contracts\MessagingProvider;
use App\Models\Member;
use App\Models\NotificationDelivery;

class NotificationService
{
    public function __construct(private readonly MessagingProvider $provider) {}

    public function send(Member $member, string $channel, string $template, string $idempotencyKey, array $payload = []): NotificationDelivery
    {
        $existing = NotificationDelivery::query()->where('organization_id', $member->organization_id)->where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $existing;
        }
        $recipient = $channel === 'email' ? $member->email : $member->phone;
        $delivery = NotificationDelivery::create(['organization_id' => $member->organization_id, 'member_id' => $member->id, 'channel' => $channel, 'template' => $template, 'status' => 'queued', 'payload' => $payload, 'idempotency_key' => $idempotencyKey]);
        try {
            $result = $this->provider->send($channel, (string) $recipient, $template, $payload);
            $delivery->update(['status' => $result['status'] === 'simulated' ? 'simulated' : 'sent', 'provider_reference' => $result['provider'] ?? null, 'sent_at' => now()]);
        } catch (\Throwable) {
            $delivery->update(['status' => 'failed', 'error' => 'Provider delivery failed.']);
        }

        return $delivery->fresh();
    }
}

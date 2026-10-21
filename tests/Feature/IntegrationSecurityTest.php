<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class IntegrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_webhook_requires_raw_signature_and_deduplicates_event(): void
    {
        Config::set('services.razorpay.webhook_secret', 'test-secret');
        $payload = json_encode(['event' => 'payment.captured'], JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $payload, 'test-secret');
        $headers = ['X-Razorpay-Signature' => $signature, 'x-razorpay-event-id' => 'evt-001', 'Content-Type' => 'application/json'];
        $this->postJson(route('integrations.razorpay.webhook'), json_decode($payload, true), $headers)->assertOk()->assertJson(['duplicate' => false]);
        $this->postJson(route('integrations.razorpay.webhook'), json_decode($payload, true), $headers)->assertOk()->assertJson(['duplicate' => true]);
        $this->assertDatabaseCount('payment_webhook_events', 1);
    }
}

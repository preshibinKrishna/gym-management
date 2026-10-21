<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Member;
use App\Models\Organization;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderAdapterTest extends TestCase
{
    use RefreshDatabase;

    public function test_fake_sms_and_whatsapp_delivery_is_idempotent_and_auditable(): void
    {
        $organization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $member = Member::factory()->create(['organization_id' => $organization->id, 'home_branch_id' => $branch->id, 'phone' => '9000000000']);
        $service = app(NotificationService::class);
        $first = $service->send($member, 'whatsapp', 'expiry-reminder', 'message-001', ['days' => 3]);
        $second = $service->send($member, 'whatsapp', 'expiry-reminder', 'message-001', ['days' => 3]);
        $this->assertSame($first->id, $second->id);
        $this->assertSame('simulated', $first->status);
        $this->assertDatabaseCount('notification_deliveries', 1);
    }
}

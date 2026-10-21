<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\User;
use App\Services\MembershipLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_freeze_and_renewal_preserve_membership_rules(): void
    {
        $organization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $member = Member::factory()->create(['organization_id' => $organization->id, 'home_branch_id' => $branch->id]);
        $plan = Plan::factory()->create(['organization_id' => $organization->id, 'duration_days' => 30, 'duration_months' => null, 'freeze_days_allowed' => 5, 'price_paise' => 10000]);
        $operator = User::factory()->create();
        $service = app(MembershipLifecycleService::class);
        $membership = $service->purchase($member, $plan, '2026-09-01', 1500, [$branch->id]);
        $this->assertSame('2026-09-30', $membership->ends_on->toDateString());
        $service->freeze($membership, '2026-09-10', '2026-09-12', 'Travel', $operator);
        $this->assertSame('2026-10-03', $membership->fresh()->ends_on->toDateString());
        $renewal = $service->renew($membership->fresh(), $operator);
        $this->assertSame('2026-10-04', $renewal->starts_on->toDateString());
        $this->assertSame('renewed', $membership->fresh()->status);
    }
}

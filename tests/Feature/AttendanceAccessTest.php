<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\User;
use App\Services\AttendanceCheckInService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AttendanceAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_in_is_idempotent_and_consumes_one_session(): void
    {
        $organization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $member = Member::factory()->create(['organization_id' => $organization->id, 'home_branch_id' => $branch->id]);
        $plan = Plan::factory()->sessionPack(1)->create(['organization_id' => $organization->id, 'eligible_branch_ids' => [$branch->id]]);
        $membership = Membership::factory()->create(['organization_id' => $organization->id, 'member_id' => $member->id, 'plan_id' => $plan->id, 'sessions_total' => 1, 'sessions_used' => 0, 'starts_on' => now()->subDay(), 'ends_on' => now()->addDay(), 'allowed_branch_ids' => [$branch->id]]);
        $operator = User::factory()->create();
        $service = app(AttendanceCheckInService::class);
        $first = $service->checkIn($member, $branch, $operator, 'check-in-1');
        $second = $service->checkIn($member, $branch, $operator, 'check-in-1');
        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, $membership->fresh()->sessions_used);
        $this->assertDatabaseCount('attendance_visits', 1);
        $this->expectException(ValidationException::class);
        $service->checkIn($member, $branch, $operator, 'check-in-2', 'Manager override');
    }
}

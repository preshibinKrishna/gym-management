<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\GuardianRelationship;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class GuardianTrainerAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_trainer_and_active_guardian_can_view_member(): void
    {
        $organization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $member = Member::factory()->create(['organization_id' => $organization->id, 'home_branch_id' => $branch->id]);
        $trainer = User::factory()->create();
        $guardian = User::factory()->create();
        $member->update(['assigned_trainer_user_id' => $trainer->id]);
        GuardianRelationship::factory()->create(['organization_id' => $member->organization_id, 'member_id' => $member->id, 'guardian_user_id' => $guardian->id]);
        app(TenantContext::class)->set($organization, $branch);
        $this->assertTrue(Gate::forUser($trainer)->allows('view', $member));
        $this->assertTrue(Gate::forUser($guardian)->allows('view', $member));
        $this->assertFalse(Gate::forUser(User::factory()->create())->allows('view', $member));
    }
}

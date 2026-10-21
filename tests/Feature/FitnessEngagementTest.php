<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchAssignment;
use App\Models\Member;
use App\Models\Organization;
use App\Models\OrganizationAffiliation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FitnessEngagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_workout_versions_are_preserved_for_a_tenant_member(): void
    {
        [$user, $organization, $branch] = $this->tenantUser(['fitness.view', 'fitness.manage']);
        $member = Member::factory()->create(['organization_id' => $organization->id, 'home_branch_id' => $branch->id]);
        $payload = ['member_id' => $member->id, 'name' => 'Strength cycle', 'starts_on' => '2026-09-09', 'exercises' => 'Squat, Bench press'];
        $this->actingAs($user)->withSession(['tenant.organization_id' => $organization->id, 'tenant.branch_id' => $branch->id])->post(route('fitness.workouts.store'), $payload)->assertRedirect();
        $this->actingAs($user)->withSession(['tenant.organization_id' => $organization->id, 'tenant.branch_id' => $branch->id])->post(route('fitness.workouts.store'), [...$payload, 'exercises' => 'Squat, Deadlift'])->assertRedirect();
        $this->assertDatabaseHas('workout_assignments', ['member_id' => $member->id, 'version' => 1]);
        $this->assertDatabaseHas('workout_assignments', ['member_id' => $member->id, 'version' => 2]);
    }

    private function tenantUser(array $permissions): array
    {
        $organization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $user = User::factory()->create();
        $role = Role::factory()->create(['scope' => 'organization']);
        $role->permissions()->sync(collect($permissions)->map(fn (string $key) => Permission::factory()->create(['key' => $key, 'name' => $key])->id));
        OrganizationAffiliation::factory()->create(['organization_id' => $organization->id, 'user_id' => $user->id]);
        BranchAssignment::factory()->create(['organization_id' => $organization->id, 'branch_id' => $branch->id, 'user_id' => $user->id]);
        RoleAssignment::factory()->create(['organization_id' => $organization->id, 'user_id' => $user->id, 'role_id' => $role->id]);

        return [$user, $organization, $branch];
    }
}

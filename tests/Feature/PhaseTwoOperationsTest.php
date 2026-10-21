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

class PhaseTwoOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_register_a_member_and_guardian_relationship(): void
    {
        [$user, $organization, $branch] = $this->tenantUser(['members.create']);
        $this->actingAs($user)->withSession(['tenant.organization_id' => $organization->id, 'tenant.branch_id' => $branch->id])->post(route('members.store'), ['home_branch_id' => $branch->id, 'member_code' => 'MEM-001', 'name' => 'Asha Rao', 'date_of_birth' => '2015-04-12', 'joined_on' => '2026-09-09', 'guardian_name' => 'Priya Rao', 'guardian_email' => 'priya.rao@example.test', 'guardian_relationship' => 'parent'])->assertRedirect();
        $this->assertDatabaseHas('members', ['organization_id' => $organization->id, 'member_code' => 'MEM-001']);
        $this->assertDatabaseHas('guardian_relationships', ['organization_id' => $organization->id, 'relationship' => 'parent']);
        $this->assertDatabaseHas('organization_affiliations', ['organization_id' => $organization->id]);
    }

    public function test_member_from_another_organization_is_not_visible(): void
    {
        [$user, $organization, $branch] = $this->tenantUser(['members.view']);
        $otherOrganization = Organization::factory()->create();
        $otherBranch = Branch::factory()->create(['organization_id' => $otherOrganization->id]);
        $otherMember = Member::factory()->create(['organization_id' => $otherOrganization->id, 'home_branch_id' => $otherBranch->id]);
        $this->actingAs($user)->withSession(['tenant.organization_id' => $organization->id, 'tenant.branch_id' => $branch->id])->get(route('members.show', $otherMember))->assertNotFound();
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

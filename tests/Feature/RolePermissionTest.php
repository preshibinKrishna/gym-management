<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchAssignment;
use App\Models\Organization;
use App\Models\OrganizationAffiliation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_role_applies_across_branches_in_same_organization(): void
    {
        [$user, $organization, $branch] = $this->tenantUser(['branches.view'], organizationWide: true);
        $secondBranch = Branch::factory()->create(['organization_id' => $organization->id]);

        $this->assertTrue($user->hasPermission('branches.view', $organization, $branch));
        $this->assertTrue($user->hasPermission('branches.view', $organization, $secondBranch));
    }

    public function test_branch_role_requires_matching_branch_assignment(): void
    {
        [$user, $organization, $branch] = $this->tenantUser(['branches.view']);
        $unassignedBranch = Branch::factory()->create(['organization_id' => $organization->id]);

        $this->assertTrue($user->hasPermission('branches.view', $organization, $branch));
        $this->assertFalse($user->hasPermission('branches.view', $organization, $unassignedBranch));
    }

    public function test_permission_does_not_cross_organizations(): void
    {
        [$user, $organization, $branch] = $this->tenantUser(['branches.view'], organizationWide: true);
        $otherOrganization = Organization::factory()->create();
        $otherBranch = Branch::factory()->create(['organization_id' => $otherOrganization->id]);

        $this->assertTrue($user->hasPermission('branches.view', $organization, $branch));
        $this->assertFalse($user->hasPermission('branches.view', $otherOrganization, $otherBranch));
    }

    /**
     * @param  array<int, string>  $permissions
     * @return array{0: User, 1: Organization, 2: Branch, 3: Role}
     */
    private function tenantUser(array $permissions, bool $organizationWide = false): array
    {
        $organization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $user = User::factory()->create();
        $role = Role::factory()->create(['key' => fake()->unique()->slug(), 'scope' => $organizationWide ? 'organization' : 'branch']);

        $permissionIds = collect($permissions)->map(function (string $key): int {
            return Permission::factory()->create([
                'key' => $key,
                'name' => $key,
                'group' => 'tests',
            ])->id;
        });

        $role->permissions()->sync($permissionIds);

        OrganizationAffiliation::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
        ]);

        BranchAssignment::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'user_id' => $user->id,
        ]);

        RoleAssignment::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $organizationWide ? null : $branch->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);

        return [$user, $organization, $branch, $role];
    }
}

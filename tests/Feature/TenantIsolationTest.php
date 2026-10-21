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

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_tenant_branch_url_returns_not_found(): void
    {
        [$user, $organization, $branch] = $this->tenantUser(['branches.view']);
        $otherOrganization = Organization::factory()->create();
        $otherBranch = Branch::factory()->create(['organization_id' => $otherOrganization->id]);

        $this->actingAs($user)
            ->withSession([
                'tenant.organization_id' => $organization->id,
                'tenant.branch_id' => $branch->id,
            ])
            ->get(route('branches.show', $otherBranch))
            ->assertNotFound();
    }

    public function test_branch_scoped_user_cannot_open_unassigned_branch_in_same_organization(): void
    {
        [$user, $organization, $branch] = $this->tenantUser(['branches.view']);
        $unassignedBranch = Branch::factory()->create(['organization_id' => $organization->id]);

        $this->actingAs($user)
            ->withSession([
                'tenant.organization_id' => $organization->id,
                'tenant.branch_id' => $branch->id,
            ])
            ->get(route('branches.show', $unassignedBranch))
            ->assertForbidden();
    }

    /**
     * @param  array<int, string>  $permissions
     * @return array{0: User, 1: Organization, 2: Branch, 3: Role}
     */
    private function tenantUser(array $permissions): array
    {
        $organization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $user = User::factory()->create();
        $role = Role::factory()->create(['key' => fake()->unique()->slug(), 'scope' => 'branch']);

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
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);

        return [$user, $organization, $branch, $role];
    }
}

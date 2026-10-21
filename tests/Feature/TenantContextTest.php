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
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_establishes_verified_tenant_context(): void
    {
        [$user, $organization, $branch] = $this->tenantUser(['branches.view']);
        $user->update(['password' => Hash::make('DevOnly!2026')]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'DevOnly!2026',
        ])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame($organization->id, session('tenant.organization_id'));
        $this->assertSame($branch->id, session('tenant.branch_id'));
    }

    public function test_missing_tenant_context_fails_closed_for_non_platform_user(): void
    {
        [$user] = $this->tenantUser(['branches.view']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_user_cannot_switch_to_unaffiliated_organization(): void
    {
        [$user, $organization, $branch] = $this->tenantUser(['branches.view']);
        $otherOrganization = Organization::factory()->create();
        $otherBranch = Branch::factory()->create(['organization_id' => $otherOrganization->id]);

        $this->actingAs($user)
            ->withSession([
                'tenant.organization_id' => $organization->id,
                'tenant.branch_id' => $branch->id,
            ])
            ->put(route('tenant-context.update'), [
                'organization_id' => $otherOrganization->id,
                'branch_id' => $otherBranch->id,
            ])
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

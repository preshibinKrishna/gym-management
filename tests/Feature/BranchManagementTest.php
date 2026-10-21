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

class BranchManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_view_branch_register(): void
    {
        [$user, $organization, $branch] = $this->tenantUser(['branches.view']);

        $this->actingAs($user)
            ->withSession([
                'tenant.organization_id' => $organization->id,
                'tenant.branch_id' => $branch->id,
            ])
            ->get(route('branches.index'))
            ->assertOk()
            ->assertSee($branch->name);
    }

    public function test_authorized_user_creates_branch_inside_current_organization(): void
    {
        [$user, $organization, $branch] = $this->tenantUser(['branches.view', 'branches.create']);
        $otherOrganization = Organization::factory()->create();

        $this->actingAs($user)
            ->withSession([
                'tenant.organization_id' => $organization->id,
                'tenant.branch_id' => $branch->id,
            ])
            ->post(route('branches.store'), [
                'organization_id' => $otherOrganization->id,
                'name' => 'Apex Koramangala',
                'code' => 'KORA',
                'timezone' => 'Asia/Kolkata',
                'capacity' => 180,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('branches', [
            'organization_id' => $organization->id,
            'code' => 'KORA',
            'name' => 'Apex Koramangala',
        ]);

        $this->assertDatabaseMissing('branches', [
            'organization_id' => $otherOrganization->id,
            'code' => 'KORA',
        ]);
    }

    public function test_duplicate_branch_code_is_scoped_to_current_organization(): void
    {
        [$user, $organization, $branch] = $this->tenantUser(['branches.view', 'branches.create']);
        Branch::factory()->create([
            'organization_id' => $organization->id,
            'code' => 'DUP',
        ]);

        $this->actingAs($user)
            ->withSession([
                'tenant.organization_id' => $organization->id,
                'tenant.branch_id' => $branch->id,
            ])
            ->post(route('branches.store'), [
                'name' => 'Duplicate Branch',
                'code' => 'DUP',
                'timezone' => 'Asia/Kolkata',
            ])
            ->assertSessionHasErrors('code');
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
        $role = Role::factory()->create(['key' => fake()->unique()->slug(), 'scope' => 'organization']);

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
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);

        return [$user, $organization, $branch, $role];
    }
}

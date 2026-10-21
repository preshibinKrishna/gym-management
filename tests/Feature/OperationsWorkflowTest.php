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

class OperationsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_operations_records_are_tenant_scoped(): void
    {
        [$user, $organization, $branch] = $this->tenantUser(['operations.view', 'operations.manage', 'cctv.manage']);
        $this->actingAs($user)->withSession(['tenant.organization_id' => $organization->id, 'tenant.branch_id' => $branch->id])->post(route('operations.store'), ['record_type' => 'vendor', 'name' => 'Reliable Repairs', 'category' => 'repairs'])->assertRedirect();
        $this->actingAs($user)->withSession(['tenant.organization_id' => $organization->id, 'tenant.branch_id' => $branch->id])->post(route('operations.store'), ['record_type' => 'asset', 'branch_id' => $branch->id, 'name' => 'Treadmill 1', 'asset_tag' => 'AST-001', 'category' => 'cardio'])->assertRedirect();
        $this->assertDatabaseHas('vendors', ['organization_id' => $organization->id, 'name' => 'Reliable Repairs']);
        $this->assertDatabaseHas('assets', ['organization_id' => $organization->id, 'asset_tag' => 'AST-001']);
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

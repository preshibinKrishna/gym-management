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

class ExportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_export_is_authorized_tenant_scoped_and_formula_safe(): void
    {
        [$user, $organization, $branch] = $this->tenantUser(['exports.create']);
        Member::factory()->create(['organization_id' => $organization->id, 'home_branch_id' => $branch->id, 'name' => '=HYPERLINK("http://evil.test")']);
        $response = $this->actingAs($user)->withSession(['tenant.organization_id' => $organization->id, 'tenant.branch_id' => $branch->id])->get(route('exports.members'));
        $response->assertOk()->assertHeader('content-disposition');
        $this->assertStringContainsString("'=HYPERLINK", $response->streamedContent());
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

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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_and_update_profile_with_avatar(): void
    {
        [$user, $organization, $branch] = $this->tenantUser();
        Storage::fake('public');
        $this->actingAs($user)->withSession(['tenant.organization_id' => $organization->id, 'tenant.branch_id' => $branch->id])->get(route('profile.edit'))->assertOk()->assertSee($user->email);
        $this->actingAs($user)->withSession(['tenant.organization_id' => $organization->id, 'tenant.branch_id' => $branch->id])->patch(route('profile.update'), ['name' => 'Updated User', 'phone' => '9000000000', 'timezone' => 'Asia/Kolkata', 'bio' => 'Coach', 'avatar' => UploadedFile::fake()->image('profile.png')])->assertRedirect()->assertSessionHas('status');
        $user->refresh();
        $this->assertSame('Updated User', $user->name);
        Storage::disk('public')->assertExists($user->avatar_path);
    }

    private function tenantUser(): array
    {
        $organization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $user = User::factory()->create();
        $role = Role::factory()->create(['scope' => 'organization']);
        $permission = Permission::factory()->create(['key' => 'branches.view', 'name' => 'View branches']);
        $role->permissions()->sync([$permission->id]);
        OrganizationAffiliation::factory()->create(['organization_id' => $organization->id, 'user_id' => $user->id]);
        BranchAssignment::factory()->create(['organization_id' => $organization->id, 'branch_id' => $branch->id, 'user_id' => $user->id]);
        RoleAssignment::factory()->create(['organization_id' => $organization->id, 'user_id' => $user->id, 'role_id' => $role->id]);

        return [$user, $organization, $branch];
    }
}

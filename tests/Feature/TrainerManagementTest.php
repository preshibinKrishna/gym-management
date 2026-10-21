<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchAssignment;
use App\Models\Organization;
use App\Models\OrganizationAffiliation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleAssignment;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrainerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_create_pay_and_deactivate_trainer(): void
    {
        [$user, $organization, $branch] = $this->tenantUser(['trainers.view', 'trainers.manage']);
        Storage::fake('public');

        $this->actingAs($user)->withSession(['tenant.organization_id' => $organization->id, 'tenant.branch_id' => $branch->id])
            ->post(route('trainers.store'), [
                'name' => 'Asha Coach',
                'email' => 'asha@example.test',
                'phone' => '9000000011',
                'specialization' => 'Strength and mobility',
                'home_branch_id' => $branch->id,
                'joined_on' => '2026-09-01',
                'employment_type' => 'salary',
                'salary_paise' => 4500000,
                'avatar' => UploadedFile::fake()->image('asha.png'),
            ])->assertRedirect();

        $trainer = Trainer::query()->where('email', 'asha@example.test')->firstOrFail();
        Storage::disk('public')->assertExists($trainer->avatar_path);

        $this->actingAs($user)->withSession(['tenant.organization_id' => $organization->id, 'tenant.branch_id' => $branch->id])
            ->post(route('trainers.payments.store', $trainer), [
                'amount_paise' => 4500000,
                'payment_type' => 'salary',
                'method' => 'bank_transfer',
                'paid_on' => '2026-09-30',
                'period_start' => '2026-09-01',
                'period_end' => '2026-09-30',
                'reference' => 'SAL-SEP-001',
            ])->assertRedirect();

        $this->actingAs($user)->withSession(['tenant.organization_id' => $organization->id, 'tenant.branch_id' => $branch->id])
            ->patch(route('trainers.status.update', $trainer), ['status' => 'inactive'])->assertRedirect();

        $this->assertDatabaseHas('trainer_payments', ['trainer_id' => $trainer->id, 'amount_paise' => 4500000]);
        $this->assertDatabaseHas('trainers', ['id' => $trainer->id, 'status' => 'inactive']);
    }

    private function tenantUser(array $permissions): array
    {
        $organization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $user = User::factory()->create();
        $role = Role::factory()->create(['scope' => 'organization']);
        $role->permissions()->sync(collect($permissions)->map(fn (string $key) => Permission::firstOrCreate(['key' => $key], ['name' => $key, 'group' => 'trainers'])->id));
        OrganizationAffiliation::factory()->create(['organization_id' => $organization->id, 'user_id' => $user->id]);
        BranchAssignment::factory()->create(['organization_id' => $organization->id, 'branch_id' => $branch->id, 'user_id' => $user->id]);
        RoleAssignment::factory()->create(['organization_id' => $organization->id, 'user_id' => $user->id, 'role_id' => $role->id]);

        return [$user, $organization, $branch];
    }
}

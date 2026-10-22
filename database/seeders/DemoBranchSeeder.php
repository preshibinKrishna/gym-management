<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchAssignment;
use App\Models\BranchOperatingInterval;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Role;
use App\Models\RoleAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoBranchSeeder extends Seeder
{
    /**
     * Fill each demo gym to 50 active branches.
     */
    public function run(): void
    {
        Organization::query()
            ->with('branches')
            ->orderBy('code')
            ->get()
            ->each(fn (Organization $organization) => $this->seedOrganization($organization));
    }

    private function seedOrganization(Organization $organization): void
    {
        $owner = User::query()->where('email', 'owner.'.mb_strtolower($organization->code).'@example.test')->first();
        $admin = User::query()->where('email', 'admin.'.mb_strtolower($organization->code).'@example.test')->first();
        $manager = User::query()->where('email', 'manager.'.mb_strtolower($organization->code).'@example.test')->first();

        if (! $owner || ! $admin || ! $manager) {
            return;
        }

        $existingBranchCount = $organization->branches->count();

        for ($branchNumber = $existingBranchCount + 1; $branchNumber <= 50; $branchNumber++) {
            $branch = Branch::factory()->create([
                'organization_id' => $organization->id,
                'name' => $organization->code.' '.fake()->city().' Fitness '.str_pad((string) $branchNumber, 2, '0', STR_PAD_LEFT),
                'code' => mb_strtoupper(mb_substr($organization->code, 0, 2)).str_pad((string) $branchNumber, 2, '0', STR_PAD_LEFT),
                'manager_user_id' => $manager->id,
            ]);

            foreach (range(1, 6) as $weekday) {
                BranchOperatingInterval::factory()->create([
                    'organization_id' => $organization->id,
                    'branch_id' => $branch->id,
                    'weekday' => $weekday,
                ]);
            }

            $this->assignStaff($organization, $branch, $owner, $admin, $manager);
        }

        $branchIds = $organization->branches()->pluck('id')->all();
        Plan::query()
            ->whereBelongsTo($organization)
            ->where('name', 'Monthly Gym Access')
            ->update(['eligible_branch_ids' => json_encode($branchIds)]);

        Membership::query()
            ->whereBelongsTo($organization)
            ->whereHas('plan', fn ($query) => $query->where('name', 'Monthly Gym Access'))
            ->update(['allowed_branch_ids' => json_encode($branchIds)]);
    }

    private function assignStaff(Organization $organization, Branch $branch, User $owner, User $admin, User $manager): void
    {
        $staff = [
            $owner,
            $admin,
            $manager,
            User::query()->where('email', 'trainer.'.mb_strtolower($organization->code).'@example.test')->first(),
            User::query()->where('email', 'receptionist.'.mb_strtolower($organization->code).'@example.test')->first(),
            User::query()->where('email', 'maintenance.'.mb_strtolower($organization->code).'@example.test')->first(),
        ];

        foreach (array_filter($staff) as $user) {
            BranchAssignment::query()->firstOrCreate(
                ['branch_id' => $branch->id, 'user_id' => $user->id],
                ['organization_id' => $organization->id, 'status' => 'active', 'starts_at' => now()->subDay()],
            );
        }

        foreach ([
            'trainer.'.mb_strtolower($organization->code).'@example.test' => 'trainer',
            'manager.'.mb_strtolower($organization->code).'@example.test' => 'branch-manager',
            'receptionist.'.mb_strtolower($organization->code).'@example.test' => 'receptionist',
            'maintenance.'.mb_strtolower($organization->code).'@example.test' => 'maintenance-staff',
        ] as $email => $roleKey) {
            $user = User::query()->where('email', $email)->first();

            if (! $user) {
                continue;
            }

            $role = Role::query()->where('key', $roleKey)->firstOrFail();
            RoleAssignment::query()->firstOrCreate(
                [
                    'organization_id' => $organization->id,
                    'branch_id' => $branch->id,
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                ],
                ['assigned_by' => $admin->id, 'starts_at' => now()->subDay()],
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\BranchAssignment;
use App\Models\Organization;
use App\Models\OrganizationAffiliation;
use App\Models\Role;
use App\Models\RoleAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoRoleAccountsSeeder extends Seeder
{
    /**
     * Seed demo accounts for the additional organization roles.
     */
    public function run(): void
    {
        Organization::query()
            ->with('branches')
            ->orderBy('code')
            ->get()
            ->each(function (Organization $organization): void {
                $branch = $organization->branches->first();
                $owner = User::query()->where('email', 'owner.'.mb_strtolower($organization->code).'@example.test')->first();

                if (! $branch || ! $owner) {
                    return;
                }

                foreach ([
                    'branch-manager' => 'manager',
                    'receptionist' => 'receptionist',
                    'accountant' => 'accountant',
                    'maintenance-staff' => 'maintenance',
                ] as $roleKey => $accountKey) {
                    $email = $accountKey.'.'.mb_strtolower($organization->code).'@example.test';
                    $user = User::query()->updateOrCreate(
                        ['email' => $email],
                        [
                            'name' => ucfirst($accountKey).' '.$organization->code,
                            'password' => Hash::make('DevOnly!2026'),
                            'status' => 'active',
                            'is_platform_super_admin' => false,
                        ],
                    );

                    OrganizationAffiliation::query()->firstOrCreate(
                        ['organization_id' => $organization->id, 'user_id' => $user->id],
                        ['status' => 'active', 'verified_at' => now(), 'invited_by' => $owner->id],
                    );

                    $role = Role::query()->where('key', $roleKey)->firstOrFail();
                    $isBranchRole = $role->scope === 'branch';

                    RoleAssignment::query()->firstOrCreate(
                        [
                            'organization_id' => $organization->id,
                            'branch_id' => $isBranchRole ? $branch->id : null,
                            'user_id' => $user->id,
                            'role_id' => $role->id,
                        ],
                        ['assigned_by' => $owner->id, 'starts_at' => now()->subDay()],
                    );

                    if ($isBranchRole) {
                        BranchAssignment::query()->firstOrCreate(
                            ['branch_id' => $branch->id, 'user_id' => $user->id],
                            ['organization_id' => $organization->id, 'status' => 'active', 'starts_at' => now()->subDay()],
                        );
                    }
                }
            });
    }
}

<?php

namespace Database\Seeders;

use App\Models\AttendanceVisit;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\BatchEnrollment;
use App\Models\Branch;
use App\Models\BranchAssignment;
use App\Models\BranchClosure;
use App\Models\BranchOperatingInterval;
use App\Models\GuardianRelationship;
use App\Models\Lead;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\OrganizationAffiliation;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\Role;
use App\Models\RoleAssignment;
use App\Models\TenantSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $permissions = collect([
                ['key' => 'platform.manage', 'name' => 'Manage platform', 'group' => 'platform'],
                ['key' => 'organizations.view', 'name' => 'View organization', 'group' => 'organization'],
                ['key' => 'organizations.update', 'name' => 'Update organization', 'group' => 'organization'],
                ['key' => 'branches.view', 'name' => 'View branches', 'group' => 'branches'],
                ['key' => 'branches.create', 'name' => 'Create branches', 'group' => 'branches'],
                ['key' => 'branches.update', 'name' => 'Update branches', 'group' => 'branches'],
                ['key' => 'roles.assign', 'name' => 'Assign scoped roles', 'group' => 'security'],
                ['key' => 'settings.manage', 'name' => 'Manage settings', 'group' => 'settings'],
                ['key' => 'members.view', 'name' => 'View members', 'group' => 'members'],
                ['key' => 'members.create', 'name' => 'Create members', 'group' => 'members'],
                ['key' => 'members.update', 'name' => 'Update members', 'group' => 'members'],
                ['key' => 'trainers.view', 'name' => 'View trainers', 'group' => 'trainers'],
                ['key' => 'trainers.manage', 'name' => 'Manage trainers and payroll', 'group' => 'trainers'],
                ['key' => 'leads.manage', 'name' => 'Manage leads', 'group' => 'leads'],
                ['key' => 'plans.view', 'name' => 'View plans', 'group' => 'plans'],
                ['key' => 'plans.manage', 'name' => 'Manage plans', 'group' => 'plans'],
                ['key' => 'memberships.manage', 'name' => 'Manage memberships', 'group' => 'memberships'],
                ['key' => 'attendance.checkin', 'name' => 'Check in members', 'group' => 'attendance'],
                ['key' => 'batches.view', 'name' => 'View batches', 'group' => 'batches'],
                ['key' => 'batches.manage', 'name' => 'Manage batches', 'group' => 'batches'],
                ['key' => 'billing.collect', 'name' => 'Collect payments', 'group' => 'finance'],
                ['key' => 'billing.refund', 'name' => 'Approve refunds', 'group' => 'finance'],
                ['key' => 'reports.view', 'name' => 'View reports', 'group' => 'reports'],
                ['key' => 'sensitive.view', 'name' => 'View sensitive records', 'group' => 'security'],
                ['key' => 'exports.create', 'name' => 'Create exports', 'group' => 'reports'],
                ['key' => 'cctv.view', 'name' => 'View CCTV streams', 'group' => 'operations'],
                ['key' => 'fitness.view', 'name' => 'View fitness records', 'group' => 'fitness'],
                ['key' => 'fitness.manage', 'name' => 'Manage fitness records', 'group' => 'fitness'],
                ['key' => 'events.view', 'name' => 'View events', 'group' => 'events'],
                ['key' => 'events.manage', 'name' => 'Manage events', 'group' => 'events'],
                ['key' => 'operations.view', 'name' => 'View operations', 'group' => 'operations'],
                ['key' => 'operations.manage', 'name' => 'Manage operations', 'group' => 'operations'],
                ['key' => 'cctv.manage', 'name' => 'Manage CCTV inventory', 'group' => 'operations'],
                ['key' => 'notifications.send', 'name' => 'Send notifications', 'group' => 'communications'],
            ])->mapWithKeys(fn (array $permission): array => [
                $permission['key'] => Permission::updateOrCreate(
                    ['key' => $permission['key']],
                    [...$permission, 'description' => 'Phase 1 permission matrix seed.'],
                ),
            ]);

            $roleDefinitions = [
                'organization-owner' => [
                    'name' => 'Organization owner',
                    'scope' => 'organization',
                    'permissions' => $permissions->keys()->reject(fn (string $key): bool => $key === 'platform.manage')->all(),
                ],
                'organization-admin' => [
                    'name' => 'Organization admin',
                    'scope' => 'organization',
                    'permissions' => ['organizations.view', 'branches.view', 'branches.create', 'branches.update', 'roles.assign', 'settings.manage', 'members.view', 'members.create', 'members.update', 'trainers.view', 'trainers.manage', 'leads.manage', 'plans.view', 'plans.manage', 'memberships.manage', 'attendance.checkin', 'batches.view', 'batches.manage', 'billing.collect', 'billing.refund', 'reports.view', 'exports.create', 'fitness.view', 'fitness.manage', 'events.view', 'events.manage', 'operations.view', 'operations.manage', 'cctv.manage', 'notifications.send'],
                ],
                'branch-manager' => [
                    'name' => 'Branch admin/manager',
                    'scope' => 'branch',
                    'permissions' => ['branches.view', 'branches.update', 'members.view', 'members.create', 'members.update', 'trainers.view', 'trainers.manage', 'leads.manage', 'plans.view', 'memberships.manage', 'attendance.checkin', 'batches.view', 'batches.manage', 'billing.collect', 'reports.view', 'fitness.view', 'fitness.manage', 'events.view', 'events.manage', 'operations.view', 'operations.manage', 'cctv.manage', 'notifications.send'],
                ],
                'receptionist' => [
                    'name' => 'Receptionist',
                    'scope' => 'branch',
                    'permissions' => ['branches.view', 'members.view', 'members.create', 'trainers.view', 'leads.manage', 'plans.view', 'memberships.manage', 'attendance.checkin', 'billing.collect', 'fitness.view', 'events.view', 'operations.view', 'notifications.send'],
                ],
                'trainer' => [
                    'name' => 'Trainer',
                    'scope' => 'branch',
                    'permissions' => ['branches.view', 'members.view', 'trainers.view', 'batches.view', 'fitness.view', 'fitness.manage', 'events.view'],
                ],
                'accountant' => [
                    'name' => 'Accountant',
                    'scope' => 'organization',
                    'permissions' => ['organizations.view', 'branches.view', 'billing.collect', 'billing.refund', 'reports.view', 'exports.create'],
                ],
                'maintenance-staff' => [
                    'name' => 'Maintenance staff',
                    'scope' => 'branch',
                    'permissions' => ['branches.view', 'operations.view', 'operations.manage'],
                ],
            ];

            $roles = collect($roleDefinitions)->mapWithKeys(function (array $definition, string $key) use ($permissions): array {
                $role = Role::updateOrCreate(
                    ['key' => $key],
                    [
                        'name' => $definition['name'],
                        'scope' => $definition['scope'],
                        'description' => 'Default system role seeded for Phase 1.',
                        'is_system' => true,
                    ],
                );

                $role->permissions()->sync($permissions->only($definition['permissions'])->pluck('id'));

                return [$key => $role];
            });

            $platformAdmin = User::factory()->create([
                'name' => 'Platform Super Admin',
                'email' => 'platform.admin@example.test',
                'password' => Hash::make('DevOnly!2026'),
                'is_platform_super_admin' => true,
            ]);

            $this->seedOrganization(
                name: 'Apex Fitness Collective',
                code: 'APEX',
                ownerEmail: 'owner.apex@example.test',
                adminEmail: 'admin.apex@example.test',
                trainerEmail: 'trainer.apex@example.test',
                branchNames: ['Apex Indiranagar', 'Apex Whitefield'],
                roles: $roles,
                platformAdmin: $platformAdmin,
            );

            $this->seedOrganization(
                name: 'Nila Strength Clubs',
                code: 'NILA',
                ownerEmail: 'owner.nila@example.test',
                adminEmail: 'admin.nila@example.test',
                trainerEmail: 'trainer.nila@example.test',
                branchNames: ['Nila Kochi', 'Nila Thrissur'],
                roles: $roles,
                platformAdmin: $platformAdmin,
            );
        });
    }

    /**
     * @param  array<int, string>  $branchNames
     * @param  Collection<string, Role>  $roles
     */
    private function seedOrganization(
        string $name,
        string $code,
        string $ownerEmail,
        string $adminEmail,
        string $trainerEmail,
        array $branchNames,
        $roles,
        User $platformAdmin,
    ): void {
        $organization = Organization::factory()->create([
            'name' => $name,
            'code' => $code,
            'legal_name' => $name.' Private Limited',
        ]);

        $owner = User::factory()->create([
            'name' => $name.' Owner',
            'email' => $ownerEmail,
            'password' => Hash::make('DevOnly!2026'),
        ]);

        $admin = User::factory()->create([
            'name' => $name.' Admin',
            'email' => $adminEmail,
            'password' => Hash::make('DevOnly!2026'),
        ]);

        $trainer = User::factory()->create([
            'name' => $name.' Trainer',
            'email' => $trainerEmail,
            'password' => Hash::make('DevOnly!2026'),
        ]);

        foreach ([$owner, $admin, $trainer] as $user) {
            OrganizationAffiliation::factory()->create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'invited_by' => $platformAdmin->id,
            ]);
        }

        RoleAssignment::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $owner->id,
            'role_id' => $roles['organization-owner']->id,
            'assigned_by' => $platformAdmin->id,
        ]);

        RoleAssignment::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $admin->id,
            'role_id' => $roles['organization-admin']->id,
            'assigned_by' => $owner->id,
        ]);

        TenantSetting::factory()->create([
            'organization_id' => $organization->id,
            'key' => 'business.defaults',
            'value' => [
                'currency' => 'INR',
                'timezone' => 'Asia/Kolkata',
                'membership_last_day_policy' => 'inclusive_branch_local_date',
            ],
        ]);

        $branches = collect();

        foreach ($branchNames as $index => $branchName) {
            $branch = Branch::factory()->create([
                'organization_id' => $organization->id,
                'name' => $branchName,
                'code' => mb_strtoupper(mb_substr($code, 0, 2)).($index + 1),
                'manager_user_id' => $admin->id,
            ]);
            $branches->push($branch);

            foreach ([$owner, $admin, $trainer] as $user) {
                BranchAssignment::factory()->create([
                    'organization_id' => $organization->id,
                    'branch_id' => $branch->id,
                    'user_id' => $user->id,
                ]);
            }

            RoleAssignment::factory()->create([
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'user_id' => $trainer->id,
                'role_id' => $roles['trainer']->id,
                'assigned_by' => $admin->id,
            ]);

            foreach (range(1, 6) as $weekday) {
                BranchOperatingInterval::factory()->create([
                    'organization_id' => $organization->id,
                    'branch_id' => $branch->id,
                    'weekday' => $weekday,
                ]);
            }

            BranchClosure::factory()->create([
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'title' => $branchName.' development stocktake',
            ]);
        }

        $monthlyPlan = Plan::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Monthly Gym Access',
            'code' => 'MONTHLY',
            'duration_months' => 1,
            'duration_days' => null,
            'price_paise' => 250000,
            'freeze_days_allowed' => 7,
            'eligible_branch_ids' => $branches->pluck('id')->all(),
        ]);

        $sessionPlan = Plan::factory()->sessionPack(10)->create([
            'organization_id' => $organization->id,
            'code' => 'PT10',
            'price_paise' => 600000,
            'eligible_branch_ids' => [$branches->first()->id],
        ]);

        $memberAccount = User::factory()->create([
            'name' => $name.' Member',
            'email' => 'member.'.mb_strtolower($code).'@example.test',
            'password' => Hash::make('DevOnly!2026'),
        ]);

        OrganizationAffiliation::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $memberAccount->id,
            'invited_by' => $admin->id,
        ]);

        $adultMember = Member::factory()->create([
            'organization_id' => $organization->id,
            'home_branch_id' => $branches->first()->id,
            'user_id' => $memberAccount->id,
            'assigned_trainer_user_id' => $trainer->id,
            'member_code' => $code.'-A001',
            'name' => $name.' Adult Member',
        ]);

        $childMember = Member::factory()->child()->create([
            'organization_id' => $organization->id,
            'home_branch_id' => $branches->first()->id,
            'assigned_trainer_user_id' => $trainer->id,
            'member_code' => $code.'-C001',
            'name' => $name.' Child Member',
        ]);

        $guardian = User::factory()->create([
            'name' => $name.' Guardian',
            'email' => 'guardian.'.mb_strtolower($code).'@example.test',
            'password' => Hash::make('DevOnly!2026'),
        ]);

        OrganizationAffiliation::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $guardian->id,
            'invited_by' => $admin->id,
        ]);

        GuardianRelationship::factory()->create([
            'organization_id' => $organization->id,
            'member_id' => $childMember->id,
            'guardian_user_id' => $guardian->id,
        ]);

        $membership = Membership::factory()->create([
            'organization_id' => $organization->id,
            'member_id' => $adultMember->id,
            'plan_id' => $monthlyPlan->id,
            'starts_on' => now()->subDays(10)->toDateString(),
            'ends_on' => now()->addDays(19)->toDateString(),
            'price_paise' => $monthlyPlan->price_paise,
            'discount_paise' => 25000,
            'total_paise' => $monthlyPlan->price_paise - 25000,
            'plan_snapshot' => $monthlyPlan->only(['name', 'code', 'duration_months', 'duration_days', 'session_limit', 'freeze_days_allowed', 'price_paise']),
            'allowed_branch_ids' => $branches->pluck('id')->all(),
        ]);

        Membership::factory()->create([
            'organization_id' => $organization->id,
            'member_id' => $childMember->id,
            'plan_id' => $sessionPlan->id,
            'sessions_total' => 10,
            'sessions_used' => 2,
            'price_paise' => $sessionPlan->price_paise,
            'total_paise' => $sessionPlan->price_paise,
            'plan_snapshot' => $sessionPlan->only(['name', 'code', 'duration_months', 'duration_days', 'session_limit', 'freeze_days_allowed', 'price_paise']),
            'allowed_branch_ids' => [$branches->first()->id],
        ]);

        $batch = Batch::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branches->first()->id,
            'trainer_user_id' => $trainer->id,
            'name' => 'Morning Strength',
            'code' => 'MORN',
        ]);

        BatchEnrollment::factory()->create([
            'organization_id' => $organization->id,
            'batch_id' => $batch->id,
            'member_id' => $adultMember->id,
        ]);

        AttendanceVisit::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branches->first()->id,
            'member_id' => $adultMember->id,
            'membership_id' => $membership->id,
            'operator_user_id' => $admin->id,
            'checked_in_at' => now()->subDay(),
        ]);

        Lead::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branches->first()->id,
            'name' => $name.' Trial Lead',
        ]);

        AuditLog::factory()->create([
            'organization_id' => $organization->id,
            'actor_user_id' => $platformAdmin->id,
            'event' => 'organization.seeded',
            'metadata' => ['label' => 'development-only'],
        ]);
    }
}

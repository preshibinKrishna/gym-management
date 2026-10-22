<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Organization;
use App\Services\FinanceService;
use Illuminate\Database\Seeder;

class MemberDirectorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Organization::query()
            ->with(['branches', 'plans'])
            ->orderBy('code')
            ->get()
            ->each(function (Organization $organization): void {
                $memberCount = Member::query()->whereBelongsTo($organization)->count();
                $membersToCreate = max(0, 50 - $memberCount);
                $branch = $organization->branches->first();
                $plan = $organization->plans->firstWhere('name', 'Monthly Gym Access');

                if ($membersToCreate === 0 || ! $branch || ! $plan) {
                    return;
                }

                $financeService = app(FinanceService::class);
                $membershipStartsOn = now()->startOfMonth()->toDateString();
                $membershipEndsOn = now()->endOfMonth()->toDateString();

                foreach (range($memberCount + 1, 50) as $memberNumber) {
                    $memberCode = $organization->code.'-M'.str_pad((string) $memberNumber, 4, '0', STR_PAD_LEFT);
                    $member = Member::factory()->create([
                        'organization_id' => $organization->id,
                        'home_branch_id' => $branch->id,
                        'member_code' => $memberCode,
                    ]);

                    Membership::factory()->create([
                        'organization_id' => $organization->id,
                        'member_id' => $member->id,
                        'plan_id' => $plan->id,
                        'starts_on' => $membershipStartsOn,
                        'ends_on' => $membershipEndsOn,
                        'price_paise' => $plan->price_paise,
                        'total_paise' => $plan->price_paise,
                        'plan_snapshot' => $plan->only(['name', 'code', 'duration_months', 'duration_days', 'session_limit', 'freeze_days_allowed', 'price_paise']),
                        'allowed_branch_ids' => $organization->branches->pluck('id')->all(),
                    ]);

                    $financeService->issueInvoice([
                        'organization_id' => $organization->id,
                        'member_id' => $member->id,
                        'number' => 'DEMO-'.$memberCode,
                        'due_on' => now()->endOfMonth()->toDateString(),
                    ], [[
                        'description' => $plan->name,
                        'quantity' => 1,
                        'unit_price_paise' => $plan->price_paise,
                    ]]);
                }
            });
    }
}

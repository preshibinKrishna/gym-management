<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipFreeze;
use App\Models\Plan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MembershipLifecycleService
{
    /**
     * @param  array<int, int>  $allowedBranchIds
     */
    public function purchase(Member $member, Plan $plan, string $startsOn, int $discountPaise, array $allowedBranchIds): Membership
    {
        if ($member->organization_id !== $plan->organization_id) {
            throw ValidationException::withMessages(['plan_id' => 'The selected plan is not available for this member.']);
        }

        $price = $plan->price_paise + $plan->joining_fee_paise;
        $discount = min($discountPaise, $price);
        $total = max(0, $price - $discount);
        $starts = CarbonImmutable::parse($startsOn);

        return DB::transaction(function () use ($member, $plan, $starts, $discount, $total, $allowedBranchIds): Membership {
            return Membership::create([
                'organization_id' => $member->organization_id,
                'member_id' => $member->id,
                'plan_id' => $plan->id,
                'starts_on' => $starts->toDateString(),
                'ends_on' => $this->calculateEndDate($starts, $plan)->toDateString(),
                'status' => 'active',
                'sessions_total' => $plan->session_limit,
                'sessions_used' => 0,
                'freeze_days_used' => 0,
                'price_paise' => $plan->price_paise + $plan->joining_fee_paise,
                'discount_paise' => $discount,
                'tax_paise' => 0,
                'total_paise' => $total,
                'plan_snapshot' => $plan->only(['name', 'code', 'type', 'duration_days', 'duration_months', 'session_limit', 'freeze_days_allowed', 'price_paise', 'joining_fee_paise']),
                'allowed_branch_ids' => array_values($allowedBranchIds),
            ]);
        });
    }

    public function renew(Membership $membership, User $actor): Membership
    {
        return DB::transaction(function () use ($membership, $actor): Membership {
            $locked = Membership::query()->whereKey($membership->id)->lockForUpdate()->firstOrFail();
            $plan = $locked->plan()->firstOrFail();
            $starts = CarbonImmutable::parse($locked->ends_on)->addDay();

            $renewal = $this->purchase($locked->member, $plan, $starts->toDateString(), 0, $locked->allowed_branch_ids);

            $locked->update(['status' => 'renewed', 'notes' => trim(($locked->notes ?? '').' Renewed by '.$actor->name)]);

            return $renewal;
        });
    }

    public function freeze(Membership $membership, string $startsOn, string $endsOn, string $reason, User $approver): MembershipFreeze
    {
        return DB::transaction(function () use ($membership, $startsOn, $endsOn, $reason, $approver): MembershipFreeze {
            $locked = Membership::query()->whereKey($membership->id)->lockForUpdate()->firstOrFail();
            $starts = CarbonImmutable::parse($startsOn);
            $ends = CarbonImmutable::parse($endsOn);
            $days = $starts->diffInDays($ends) + 1;
            $allowance = (int) ($locked->plan_snapshot['freeze_days_allowed'] ?? 0);

            if ($starts->greaterThan($ends)) {
                throw ValidationException::withMessages(['ends_on' => 'Freeze end date must be on or after the start date.']);
            }

            if ($locked->freeze_days_used + $days > $allowance) {
                throw ValidationException::withMessages(['ends_on' => 'Freeze exceeds the allowed days for this membership.']);
            }

            $freeze = MembershipFreeze::create([
                'organization_id' => $locked->organization_id,
                'membership_id' => $locked->id,
                'starts_on' => $starts->toDateString(),
                'ends_on' => $ends->toDateString(),
                'status' => 'approved',
                'approved_by' => $approver->id,
                'reason' => $reason,
            ]);

            $locked->update([
                'ends_on' => CarbonImmutable::parse($locked->ends_on)->addDays($days)->toDateString(),
                'freeze_days_used' => $locked->freeze_days_used + $days,
                'status' => 'frozen',
            ]);

            return $freeze;
        });
    }

    private function calculateEndDate(CarbonImmutable $starts, Plan $plan): CarbonImmutable
    {
        if ($plan->duration_months !== null) {
            return $starts->addMonthsNoOverflow($plan->duration_months)->subDay();
        }

        $days = max(1, $plan->duration_days ?? 1);

        return $starts->addDays($days - 1);
    }
}

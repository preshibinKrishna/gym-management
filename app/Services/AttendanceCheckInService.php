<?php

namespace App\Services;

use App\Models\AttendanceVisit;
use App\Models\Branch;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceCheckInService
{
    public function checkIn(Member $member, Branch $branch, User $operator, string $idempotencyKey, ?string $overrideReason = null, ?int $batchId = null): AttendanceVisit
    {
        if ($member->organization_id !== $branch->organization_id) {
            throw ValidationException::withMessages(['member_id' => 'The selected member does not belong to this branch organization.']);
        }

        return DB::transaction(function () use ($member, $branch, $operator, $idempotencyKey, $overrideReason, $batchId): AttendanceVisit {
            $existing = AttendanceVisit::query()
                ->where('organization_id', $member->organization_id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                return $existing;
            }

            $hasOpenVisit = AttendanceVisit::query()
                ->whereBelongsTo($member)
                ->whereNull('checked_out_at')
                ->exists();

            if ($hasOpenVisit && $overrideReason === null) {
                throw ValidationException::withMessages(['member_id' => 'Member already has an open visit.']);
            }

            $membership = $member->activeMembershipForBranch($branch);

            if ($membership === null) {
                throw ValidationException::withMessages(['member_id' => 'No active membership allows access to this branch today.']);
            }

            if ($membership->sessions_total !== null && $membership->remainingSessions() <= 0) {
                throw ValidationException::withMessages(['member_id' => 'This membership has no remaining sessions.']);
            }

            $sessionConsumed = false;

            if ($membership->sessions_total !== null) {
                $membership->increment('sessions_used');
                $sessionConsumed = true;
            }

            return AttendanceVisit::create([
                'organization_id' => $member->organization_id,
                'branch_id' => $branch->id,
                'member_id' => $member->id,
                'membership_id' => $membership->id,
                'batch_id' => $batchId,
                'operator_user_id' => $operator->id,
                'checked_in_at' => now(),
                'source' => 'manual',
                'idempotency_key' => $idempotencyKey,
                'session_consumed' => $sessionConsumed,
                'override_reason' => $overrideReason,
            ]);
        });
    }
}

<?php

namespace App\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'home_branch_id', 'user_id', 'assigned_trainer_user_id', 'member_code', 'name', 'avatar_path', 'date_of_birth', 'gender', 'phone', 'email', 'address', 'emergency_contact_name', 'emergency_contact_phone', 'joined_on', 'status', 'goals', 'consents', 'private_health_notes'])]
class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'joined_on' => 'date',
            'goals' => 'array',
            'consents' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function homeBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'home_branch_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_trainer_user_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function guardianRelationships(): HasMany
    {
        return $this->hasMany(GuardianRelationship::class);
    }

    public function batchEnrollments(): HasMany
    {
        return $this->hasMany(BatchEnrollment::class);
    }

    public function attendanceVisits(): HasMany
    {
        return $this->hasMany(AttendanceVisit::class);
    }

    public function workoutAssignments(): HasMany
    {
        return $this->hasMany(WorkoutAssignment::class);
    }

    public function mealPlans(): HasMany
    {
        return $this->hasMany(MealPlan::class);
    }

    public function fitnessGoals(): HasMany
    {
        return $this->hasMany(FitnessGoal::class);
    }

    public function activeMembershipForBranch(Branch $branch, ?string $businessDate = null): ?Membership
    {
        $date = $businessDate ?? now($branch->timezone)->toDateString();

        return $this->memberships()
            ->where('status', 'active')
            ->whereDate('starts_on', '<=', $date)
            ->whereDate('ends_on', '>=', $date)
            ->orderByDesc('ends_on')
            ->get()
            ->first(fn (Membership $membership): bool => in_array($branch->id, $membership->allowed_branch_ids ?? [], true));
    }

    public function monthlyPaymentStatus(): string
    {
        if ($this->invoices->isEmpty()) {
            return 'not_billed';
        }

        return $this->monthlyPaidPaise() >= $this->monthlyBilledPaise() ? 'paid' : ($this->monthlyPaidPaise() > 0 ? 'partial' : 'unpaid');
    }

    public function monthlyBilledPaise(): int
    {
        return (int) $this->invoices->sum('total_paise');
    }

    public function monthlyPaidPaise(): int
    {
        return (int) $this->invoices->sum('paid_paise');
    }
}

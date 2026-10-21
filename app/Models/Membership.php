<?php

namespace App\Models;

use Database\Factories\MembershipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'member_id', 'plan_id', 'payer_member_id', 'starts_on', 'ends_on', 'status', 'sessions_total', 'sessions_used', 'freeze_days_used', 'price_paise', 'discount_paise', 'tax_paise', 'total_paise', 'plan_snapshot', 'allowed_branch_ids', 'notes'])]
class Membership extends Model
{
    /** @use HasFactory<MembershipFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'sessions_total' => 'integer',
            'sessions_used' => 'integer',
            'freeze_days_used' => 'integer',
            'price_paise' => 'integer',
            'discount_paise' => 'integer',
            'tax_paise' => 'integer',
            'total_paise' => 'integer',
            'plan_snapshot' => 'array',
            'allowed_branch_ids' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function freezes(): HasMany
    {
        return $this->hasMany(MembershipFreeze::class);
    }

    public function remainingSessions(): ?int
    {
        if ($this->sessions_total === null) {
            return null;
        }

        return max(0, $this->sessions_total - $this->sessions_used);
    }
}

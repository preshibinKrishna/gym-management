<?php

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'name', 'code', 'type', 'duration_days', 'duration_months', 'session_limit', 'freeze_days_allowed', 'price_paise', 'joining_fee_paise', 'age_group', 'eligible_branch_ids', 'features', 'is_active'])]
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'duration_months' => 'integer',
            'session_limit' => 'integer',
            'freeze_days_allowed' => 'integer',
            'price_paise' => 'integer',
            'joining_fee_paise' => 'integer',
            'eligible_branch_ids' => 'array',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }
}

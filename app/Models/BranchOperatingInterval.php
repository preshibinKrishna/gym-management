<?php

namespace App\Models;

use Database\Factories\BranchOperatingIntervalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'branch_id', 'weekday', 'opens_at', 'closes_at', 'closes_next_day', 'eligibility', 'effective_from', 'effective_until'])]
class BranchOperatingInterval extends Model
{
    /** @use HasFactory<BranchOperatingIntervalFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
            'closes_next_day' => 'boolean',
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}

<?php

namespace App\Models;

use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'name', 'code', 'status', 'timezone', 'phone', 'email', 'address', 'city', 'state', 'postal_code', 'capacity', 'facilities', 'terms_summary', 'manager_user_id'])]
class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'facilities' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(BranchAssignment::class);
    }

    public function operatingIntervals(): HasMany
    {
        return $this->hasMany(BranchOperatingInterval::class);
    }

    public function closures(): HasMany
    {
        return $this->hasMany(BranchClosure::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'home_branch_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function attendanceVisits(): HasMany
    {
        return $this->hasMany(AttendanceVisit::class);
    }

    public function trainers(): HasMany
    {
        return $this->hasMany(Trainer::class, 'home_branch_id');
    }
}

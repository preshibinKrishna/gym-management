<?php

namespace App\Models;

use Database\Factories\BatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'branch_id', 'trainer_user_id', 'name', 'code', 'starts_at', 'ends_at', 'ends_next_day', 'capacity', 'eligibility', 'weekdays', 'status'])]
class Batch extends Model
{
    /** @use HasFactory<BatchFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'ends_next_day' => 'boolean',
            'capacity' => 'integer',
            'weekdays' => 'array',
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

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_user_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(BatchEnrollment::class);
    }

    public function attendanceVisits(): HasMany
    {
        return $this->hasMany(AttendanceVisit::class);
    }
}

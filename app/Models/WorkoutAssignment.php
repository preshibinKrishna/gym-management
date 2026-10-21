<?php

namespace App\Models;

use Database\Factories\WorkoutAssignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'member_id', 'trainer_user_id', 'name', 'starts_on', 'ends_on', 'exercises', 'instructions', 'status', 'version'])]
class WorkoutAssignment extends Model
{
    /** @use HasFactory<WorkoutAssignmentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'exercises' => 'array'];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}

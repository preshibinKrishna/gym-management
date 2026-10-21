<?php

namespace App\Models;

use Database\Factories\FitnessGoalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'member_id', 'trainer_user_id', 'category', 'title', 'notes', 'starts_on', 'target_on', 'review_on', 'status', 'milestones'])]
class FitnessGoal extends Model
{
    /** @use HasFactory<FitnessGoalFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'target_on' => 'date', 'review_on' => 'date', 'milestones' => 'array'];
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

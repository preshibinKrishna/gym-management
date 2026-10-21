<?php

namespace App\Models;

use Database\Factories\MealPlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'member_id', 'author_user_id', 'name', 'starts_on', 'ends_on', 'meals', 'preferences', 'status', 'version'])]
class MealPlan extends Model
{
    /** @use HasFactory<MealPlanFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'meals' => 'array', 'preferences' => 'array'];
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

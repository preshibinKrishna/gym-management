<?php

namespace App\Models;

use Database\Factories\GymEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'branch_id', 'organizer_user_id', 'name', 'description', 'starts_at', 'ends_at', 'capacity', 'price_paise', 'status'])]
class GymEvent extends Model
{
    /** @use HasFactory<GymEventFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}

<?php

namespace App\Models;

use Database\Factories\OfferFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'code', 'name', 'kind', 'value', 'starts_on', 'ends_on', 'eligibility', 'usage_limit', 'used_count', 'stackable', 'is_active'])]
class Offer extends Model
{
    /** @use HasFactory<OfferFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'eligibility' => 'array', 'stackable' => 'boolean', 'is_active' => 'boolean'];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}

<?php

namespace App\Models;

use Database\Factories\GstSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'legal_name', 'gstin', 'state_code', 'enabled', 'effective_from', 'effective_until', 'settings'])]
class GstSetting extends Model
{
    /** @use HasFactory<GstSettingFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['effective_from' => 'date', 'effective_until' => 'date', 'enabled' => 'boolean', 'settings' => 'array'];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}

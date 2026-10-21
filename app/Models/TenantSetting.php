<?php

namespace App\Models;

use Database\Factories\TenantSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'branch_id', 'key', 'value', 'effective_from', 'effective_until'])]
class TenantSetting extends Model
{
    /** @use HasFactory<TenantSettingFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'effective_from' => 'datetime',
            'effective_until' => 'datetime',
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

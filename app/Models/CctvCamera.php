<?php

namespace App\Models;

use Database\Factories\CctvCameraFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'branch_id', 'name', 'zone', 'brand', 'model', 'private_host', 'protocol', 'nvr_channel', 'status', 'retention_metadata'])]
class CctvCamera extends Model
{
    /** @use HasFactory<CctvCameraFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['retention_metadata' => 'array'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}

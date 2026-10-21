<?php

namespace App\Models;

use Database\Factories\AssetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'branch_id', 'vendor_id', 'asset_tag', 'name', 'category', 'muscle_groups', 'brand', 'model', 'serial_number', 'zone', 'acquisition_cost_paise', 'purchased_on', 'warranty_until', 'condition', 'service_status', 'metadata'])]
class Asset extends Model
{
    /** @use HasFactory<AssetFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['muscle_groups' => 'array', 'metadata' => 'array', 'purchased_on' => 'date', 'warranty_until' => 'date'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}

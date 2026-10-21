<?php

namespace App\Models;

use Database\Factories\MaintenanceWorkOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'branch_id', 'asset_id', 'vendor_id', 'title', 'description', 'severity', 'status', 'due_on', 'cost_paise', 'completed_at'])]
class MaintenanceWorkOrder extends Model
{
    /** @use HasFactory<MaintenanceWorkOrderFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['due_on' => 'date', 'completed_at' => 'datetime'];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}

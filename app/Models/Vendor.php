<?php

namespace App\Models;

use Database\Factories\VendorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'name', 'category', 'phone', 'email', 'address', 'gstin', 'terms', 'status'])]
class Vendor extends Model
{
    /** @use HasFactory<VendorFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['terms' => 'array'];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}

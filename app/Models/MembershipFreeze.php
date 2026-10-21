<?php

namespace App\Models;

use Database\Factories\MembershipFreezeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'membership_id', 'starts_on', 'ends_on', 'status', 'approved_by', 'reason'])]
class MembershipFreeze extends Model
{
    /** @use HasFactory<MembershipFreezeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }
}

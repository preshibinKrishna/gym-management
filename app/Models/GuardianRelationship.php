<?php

namespace App\Models;

use Database\Factories\GuardianRelationshipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'member_id', 'guardian_user_id', 'relationship', 'can_view_profile', 'can_pay', 'revoked_at', 'review_required_at'])]
class GuardianRelationship extends Model
{
    /** @use HasFactory<GuardianRelationshipFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'can_view_profile' => 'boolean',
            'can_pay' => 'boolean',
            'revoked_at' => 'datetime',
            'review_required_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_user_id');
    }
}

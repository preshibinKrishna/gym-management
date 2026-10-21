<?php

namespace App\Models;

use Database\Factories\NotificationDeliveryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'member_id', 'channel', 'template', 'status', 'provider_reference', 'error', 'payload', 'sent_at', 'idempotency_key'])]
class NotificationDelivery extends Model
{
    /** @use HasFactory<NotificationDeliveryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['payload' => 'array', 'sent_at' => 'datetime'];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}

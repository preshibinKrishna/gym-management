<?php

namespace App\Models;

use Database\Factories\PaymentWebhookEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'provider', 'event_id', 'event_type', 'status', 'payload', 'processed_at'])]
class PaymentWebhookEvent extends Model
{
    /** @use HasFactory<PaymentWebhookEventFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['payload' => 'array', 'processed_at' => 'datetime'];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}

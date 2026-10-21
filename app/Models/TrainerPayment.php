<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'trainer_id', 'amount_paise', 'payment_type', 'method', 'paid_on', 'period_start', 'period_end', 'reference', 'notes'])]
class TrainerPayment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['paid_on' => 'date', 'period_start' => 'date', 'period_end' => 'date'];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }
}

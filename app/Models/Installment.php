<?php

namespace App\Models;

use Database\Factories\InstallmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'invoice_id', 'sequence', 'amount_paise', 'due_on', 'status', 'paid_on'])]
class Installment extends Model
{
    /** @use HasFactory<InstallmentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['due_on' => 'date', 'paid_on' => 'date'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}

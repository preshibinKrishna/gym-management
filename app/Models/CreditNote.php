<?php

namespace App\Models;

use Database\Factories\CreditNoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'invoice_id', 'number', 'status', 'issued_on', 'amount_paise', 'reason'])]
class CreditNote extends Model
{
    /** @use HasFactory<CreditNoteFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['issued_on' => 'date'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}

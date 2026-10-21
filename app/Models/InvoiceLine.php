<?php

namespace App\Models;

use Database\Factories\InvoiceLineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['invoice_id', 'description', 'quantity', 'unit_price_paise', 'discount_paise', 'tax_paise', 'total_paise', 'metadata'])]
class InvoiceLine extends Model
{
    /** @use HasFactory<InvoiceLineFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}

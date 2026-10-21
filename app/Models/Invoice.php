<?php

namespace App\Models;

use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'member_id', 'number', 'status', 'issued_on', 'due_on', 'subtotal_paise', 'discount_paise', 'tax_paise', 'total_paise', 'paid_paise', 'tax_snapshot', 'einvoice_status', 'irn', 'einvoice_payload', 'einvoice_response'])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['issued_on' => 'date', 'due_on' => 'date', 'tax_snapshot' => 'array', 'einvoice_payload' => 'array', 'einvoice_response' => 'array'];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    public function balancePaise(): int
    {
        return max(0, $this->total_paise - $this->paid_paise);
    }
}

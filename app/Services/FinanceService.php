<?php

namespace App\Services;

use App\Models\CreditNote;
use App\Models\GstSetting;
use App\Models\Invoice;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\Refund;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinanceService
{
    public function issueInvoice(array $data, array $lines): Invoice
    {
        return DB::transaction(function () use ($data, $lines): Invoice {
            $subtotal = collect($lines)->sum(fn (array $line): int => $line['quantity'] * $line['unit_price_paise']);
            $discount = min($subtotal, (int) ($data['discount_paise'] ?? 0));
            $tax = max(0, (int) ($data['tax_paise'] ?? 0));
            $invoice = Invoice::create([...$data, 'status' => 'issued', 'issued_on' => $data['issued_on'] ?? now()->toDateString(), 'subtotal_paise' => $subtotal, 'discount_paise' => $discount, 'tax_paise' => $tax, 'total_paise' => max(0, $subtotal - $discount + $tax), 'paid_paise' => 0]);
            foreach ($lines as $line) {
                $lineTotal = max(0, $line['quantity'] * $line['unit_price_paise'] - ($line['discount_paise'] ?? 0) + ($line['tax_paise'] ?? 0));
                $invoice->lines()->create([...$line, 'total_paise' => $lineTotal]);
            }

            return $invoice->fresh('lines');
        });
    }

    public function createInstallmentSchedule(Invoice $invoice, int $count, string $firstDueOn, int $intervalDays = 30): array
    {
        if ($count < 1) {
            throw ValidationException::withMessages(['count' => 'At least one installment is required.']);
        }

        return DB::transaction(function () use ($invoice, $count, $firstDueOn, $intervalDays): array {
            $base = intdiv($invoice->total_paise, $count);
            $remainder = $invoice->total_paise - ($base * $count);
            $date = CarbonImmutable::parse($firstDueOn);
            $rows = [];
            for ($sequence = 1; $sequence <= $count; $sequence++) {
                $amount = $base + ($sequence === $count ? $remainder : 0);
                $rows[] = $invoice->installments()->create(['organization_id' => $invoice->organization_id, 'sequence' => $sequence, 'amount_paise' => $amount, 'due_on' => $date->toDateString()]);
                $date = $date->addDays($intervalDays);
            }

            return $rows;
        });
    }

    public function redeemOffer(Offer $offer, int $basePaise, string $date): int
    {
        return DB::transaction(function () use ($offer, $basePaise, $date): int {
            $locked = Offer::query()->lockForUpdate()->findOrFail($offer->id);
            if (! $locked->is_active || $date < $locked->starts_on->toDateString() || $date > $locked->ends_on->toDateString() || ($locked->usage_limit !== null && $locked->used_count >= $locked->usage_limit)) {
                throw ValidationException::withMessages(['offer' => 'This offer is not currently redeemable.']);
            }
            $discount = $locked->kind === 'percentage' ? intdiv($basePaise * min(100, $locked->value), 100) : min($basePaise, $locked->value);
            $locked->increment('used_count');

            return $discount;
        });
    }

    public function effectiveGstSetting(int $organizationId, string $date): ?GstSetting
    {
        return GstSetting::query()->where('organization_id', $organizationId)->where('effective_from', '<=', $date)->where(fn ($query) => $query->whereNull('effective_until')->orWhere('effective_until', '>=', $date))->latest('effective_from')->first();
    }

    public function collectionsSummary(int $organizationId, string $from, string $to): array
    {
        $query = Payment::query()->where('organization_id', $organizationId)->whereBetween('received_on', [$from, $to]);

        return ['payment_count' => (clone $query)->count(), 'collected_paise' => (int) (clone $query)->sum('amount_paise'), 'by_method' => (clone $query)->selectRaw('method, SUM(amount_paise) as total_paise')->groupBy('method')->pluck('total_paise', 'method')->map(fn ($value): int => (int) $value)->all()];
    }

    public function receivePayment(array $data, array $allocations): Payment
    {
        return DB::transaction(function () use ($data, $allocations): Payment {
            if ($existing = Payment::query()->where('organization_id', $data['organization_id'])->where('idempotency_key', $data['idempotency_key'])->first()) {
                return $existing;
            }
            $payment = Payment::create([...$data, 'received_on' => $data['received_on'] ?? now()->toDateString(), 'status' => 'received']);
            $allocated = 0;
            foreach ($allocations as $allocation) {
                $invoice = Invoice::query()->where('organization_id', $payment->organization_id)->lockForUpdate()->findOrFail($allocation['invoice_id']);
                $amount = min((int) $allocation['amount_paise'], $invoice->balancePaise(), $payment->amount_paise - $allocated);
                if ($amount <= 0) {
                    continue;
                }
                $payment->allocations()->create(['organization_id' => $payment->organization_id, 'invoice_id' => $invoice->id, 'amount_paise' => $amount]);
                $invoice->increment('paid_paise', $amount);
                $invoice->refresh();
                $invoice->update(['status' => $invoice->paid_paise >= $invoice->total_paise ? 'paid' : 'partially_paid']);
                $this->syncInstallments($invoice);
                $allocated += $amount;
            }
            if ($allocated > $payment->amount_paise) {
                throw ValidationException::withMessages(['amount_paise' => 'Payment allocations exceed the received amount.']);
            }

            return $payment->fresh('allocations');
        });
    }

    public function refundPayment(Payment $payment, int $amountPaise, string $idempotencyKey, string $reason): Refund
    {
        return DB::transaction(function () use ($payment, $amountPaise, $idempotencyKey, $reason): Refund {
            if ($existing = Refund::query()->where('organization_id', $payment->organization_id)->where('idempotency_key', $idempotencyKey)->first()) {
                return $existing;
            }
            $payment = Payment::query()->where('organization_id', $payment->organization_id)->lockForUpdate()->findOrFail($payment->id);
            $alreadyRefunded = (int) $payment->refunds()->whereIn('status', ['requested', 'refunded'])->sum('amount_paise');
            $amount = min($amountPaise, max(0, $payment->amount_paise - $alreadyRefunded));
            if ($amount < 1) {
                throw ValidationException::withMessages(['amount_paise' => 'Refund exceeds the refundable payment balance.']);
            }
            $refund = Refund::create(['organization_id' => $payment->organization_id, 'payment_id' => $payment->id, 'amount_paise' => $amount, 'idempotency_key' => $idempotencyKey, 'reason' => $reason, 'status' => 'refunded', 'refunded_at' => now()]);
            $remaining = $amount;
            foreach ($payment->allocations()->with('invoice')->orderByDesc('id')->get() as $allocation) {
                if ($remaining < 1) {
                    break;
                }
                $reversal = min($remaining, $allocation->amount_paise);
                $invoice = Invoice::query()->lockForUpdate()->find($allocation->invoice_id);
                if ($invoice) {
                    $invoice->decrement('paid_paise', $reversal);
                    $invoice->refresh();
                    $invoice->update(['status' => $invoice->paid_paise >= $invoice->total_paise ? 'paid' : ($invoice->paid_paise > 0 ? 'partially_paid' : 'issued')]);
                    $this->syncInstallments($invoice);
                }
                $remaining -= $reversal;
            }
            $payment->update(['status' => $alreadyRefunded + $amount >= $payment->amount_paise ? 'refunded' : 'partially_refunded']);

            return $refund;
        });
    }

    private function syncInstallments(Invoice $invoice): void
    {
        $covered = 0;
        foreach ($invoice->installments()->orderBy('sequence')->get() as $installment) {
            $covered += $installment->amount_paise;
            $installment->update($invoice->paid_paise >= $covered ? ['status' => 'paid', 'paid_on' => now()->toDateString()] : ['status' => 'pending', 'paid_on' => null]);
        }
    }

    public function issueCreditNote(Invoice $invoice, int $amountPaise, string $number, string $reason): CreditNote
    {
        return DB::transaction(function () use ($invoice, $amountPaise, $number, $reason): CreditNote {
            $amount = min($amountPaise, $invoice->total_paise);
            if ($amount < 1) {
                throw ValidationException::withMessages(['amount_paise' => 'Credit note amount must be positive.']);
            }

            $credit = CreditNote::create(['organization_id' => $invoice->organization_id, 'invoice_id' => $invoice->id, 'number' => $number, 'issued_on' => now()->toDateString(), 'amount_paise' => $amount, 'reason' => $reason]);
            $invoice->update(['total_paise' => max(0, $invoice->total_paise - $amount), 'status' => $invoice->paid_paise >= max(0, $invoice->total_paise - $amount) ? 'paid' : 'partially_paid']);

            return $credit;
        });
    }
}

<?php

namespace Tests\Feature;

use App\Models\Offer;
use App\Models\Organization;
use App\Services\FinanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FinanceLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_totals_and_payment_allocations_remain_distinct_and_idempotent(): void
    {
        $organization = Organization::factory()->create();
        $service = app(FinanceService::class);
        $invoice = $service->issueInvoice(['organization_id' => $organization->id, 'number' => 'INV-001', 'discount_paise' => 500, 'tax_paise' => 100], [['description' => 'Monthly access', 'quantity' => 2, 'unit_price_paise' => 5000]]);

        $this->assertSame(10000, $invoice->subtotal_paise);
        $this->assertSame(9600, $invoice->total_paise);

        $payment = $service->receivePayment(['organization_id' => $organization->id, 'amount_paise' => 4000, 'method' => 'upi', 'idempotency_key' => 'pay-001'], [['invoice_id' => $invoice->id, 'amount_paise' => 4000]]);
        $replay = $service->receivePayment(['organization_id' => $organization->id, 'amount_paise' => 4000, 'method' => 'upi', 'idempotency_key' => 'pay-001'], [['invoice_id' => $invoice->id, 'amount_paise' => 4000]]);

        $this->assertSame($payment->id, $replay->id);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'paid_paise' => 4000, 'status' => 'partially_paid']);
        $this->assertDatabaseCount('payments', 1);

        $credit = $service->issueCreditNote($invoice->fresh(), 1000, 'CN-001', 'Approved adjustment');
        $this->assertSame(1000, $credit->amount_paise);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'total_paise' => 8600]);

        $schedule = $service->createInstallmentSchedule($invoice->fresh(), 3, '2026-10-01');
        $this->assertSame(8600, collect($schedule)->sum('amount_paise'));
        $offer = Offer::factory()->create(['organization_id' => $organization->id, 'code' => 'WELCOME10', 'name' => 'Welcome discount', 'kind' => 'percentage', 'value' => 10, 'starts_on' => '2026-01-01', 'ends_on' => '2026-12-31', 'usage_limit' => 1]);
        $this->assertSame(1000, $service->redeemOffer($offer, 10000, '2026-09-09'));
        $this->expectException(ValidationException::class);
        $service->redeemOffer($offer, 10000, '2026-09-09');
    }

    public function test_refund_reverses_allocated_cash_and_marks_installment_unpaid(): void
    {
        $organization = Organization::factory()->create();
        $service = app(FinanceService::class);
        $invoice = $service->issueInvoice(['organization_id' => $organization->id, 'number' => 'INV-REFUND'], [['description' => 'Training', 'quantity' => 1, 'unit_price_paise' => 5000]]);
        $service->createInstallmentSchedule($invoice, 2, '2026-09-10');
        $payment = $service->receivePayment(['organization_id' => $organization->id, 'amount_paise' => 5000, 'method' => 'cash', 'idempotency_key' => 'pay-refund'], [['invoice_id' => $invoice->id, 'amount_paise' => 5000]]);
        $refund = $service->refundPayment($payment, 5000, 'refund-001', 'Customer cancellation');
        $this->assertSame('refunded', $refund->status);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'refunded']);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'paid_paise' => 0, 'status' => 'issued']);
        $this->assertDatabaseHas('installments', ['invoice_id' => $invoice->id, 'status' => 'pending']);
    }
}

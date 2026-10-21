<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGateway;
use App\Http\Requests\CreatePaymentIntentRequest;
use App\Http\Requests\SendNotificationRequest;
use App\Http\Requests\StoreRefundRequest;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Services\FakeEInvoiceGateway;
use App\Services\FinanceService;
use App\Services\NotificationService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function paymentIntent(CreatePaymentIntentRequest $request, PaymentGateway $gateway): JsonResponse
    {
        return response()->json($gateway->createIntent($request->integer('amount_paise'), $request->string('reference')->toString()));
    }

    public function refund(StoreRefundRequest $request, TenantContext $tenantContext, FinanceService $financeService): JsonResponse
    {
        $payment = Payment::query()->where('organization_id', $tenantContext->requireOrganization()->id)->findOrFail($request->integer('payment_id'));
        $refund = $financeService->refundPayment($payment, $request->integer('amount_paise'), $request->string('idempotency_key')->toString(), $request->string('reason')->toString());

        return response()->json(['id' => $refund->id, 'status' => $refund->status]);
    }

    public function notification(SendNotificationRequest $request, TenantContext $tenantContext, NotificationService $service): JsonResponse
    {
        $member = Member::query()->where('organization_id', $tenantContext->requireOrganization()->id)->findOrFail($request->integer('member_id'));
        $delivery = $service->send($member, $request->string('channel')->toString(), $request->string('template')->toString(), $request->string('idempotency_key')->toString(), $request->input('payload', []));

        return response()->json(['id' => $delivery->id, 'status' => $delivery->status]);
    }

    public function eInvoice(Request $request, TenantContext $tenantContext): JsonResponse
    {
        abort_unless($request->user()->hasPermission('billing.collect', $tenantContext->requireOrganization()), 403);
        $invoice = Invoice::query()->where('organization_id', $tenantContext->requireOrganization()->id)->findOrFail($request->integer('invoice_id'));
        $result = (new FakeEInvoiceGateway)->register($invoice);
        $invoice->update(['einvoice_status' => $result['status'], 'irn' => $result['irn'] ?? null, 'einvoice_payload' => $invoice->toArray(), 'einvoice_response' => $result]);

        return response()->json($result);
    }

    public function paymentWebhook(Request $request): JsonResponse
    {
        $raw = $request->getContent();
        $secret = (string) config('services.razorpay.webhook_secret');
        $signature = (string) $request->header('X-Razorpay-Signature');
        abort_unless($secret !== '' && $signature !== '' && hash_equals(hash_hmac('sha256', $raw, $secret), $signature), 401);
        $eventId = (string) $request->header('x-razorpay-event-id');
        abort_unless($eventId !== '', 400);
        $event = PaymentWebhookEvent::firstOrCreate(['provider' => 'razorpay', 'event_id' => $eventId], ['event_type' => (string) $request->input('event'), 'payload' => json_decode($raw, true) ?: [], 'status' => 'received']);
        if ($event->wasRecentlyCreated) {
            $event->update(['status' => 'processed', 'processed_at' => now()]);
        }

        return response()->json(['accepted' => true, 'duplicate' => ! $event->wasRecentlyCreated]);
    }
}

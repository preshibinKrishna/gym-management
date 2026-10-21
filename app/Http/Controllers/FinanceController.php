<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Invoice;
use App\Models\Member;
use App\Services\FinanceService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(TenantContext $tenantContext): View
    {
        $organization = $tenantContext->requireOrganization();
        abort_unless(request()->user()->hasPermission('billing.collect', $organization), 403);

        $invoices = Invoice::query()->whereBelongsTo($organization)->with('member')->latest()->paginate(15)->withQueryString();

        return view('finance.index', ['invoices' => $invoices, 'members' => Member::query()->whereBelongsTo($organization)->orderBy('name')->get(), 'invoiceTotal' => Invoice::whereBelongsTo($organization)->count(), 'outstandingPaise' => Invoice::whereBelongsTo($organization)->sum('total_paise') - Invoice::whereBelongsTo($organization)->sum('paid_paise'), 'collectedPaise' => Invoice::whereBelongsTo($organization)->sum('paid_paise')]);
    }

    public function storeInvoice(StoreInvoiceRequest $request, TenantContext $tenantContext, FinanceService $service): RedirectResponse
    {
        $invoice = $service->issueInvoice(['organization_id' => $tenantContext->requireOrganization()->id, ...$request->safe()->only(['member_id', 'number', 'due_on', 'discount_paise', 'tax_paise'])], [['description' => $request->string('description')->toString(), 'quantity' => $request->integer('quantity'), 'unit_price_paise' => $request->integer('unit_price_paise')]]);

        return redirect()->route('finance.index')->with('status', 'Invoice '.$invoice->number.' issued.');
    }

    public function storePayment(StorePaymentRequest $request, TenantContext $tenantContext, FinanceService $service): RedirectResponse
    {
        $service->receivePayment(['organization_id' => $tenantContext->requireOrganization()->id, ...$request->safe()->only(['member_id', 'amount_paise', 'method', 'idempotency_key'])], [['invoice_id' => $request->integer('invoice_id'), 'amount_paise' => $request->integer('amount_paise')]]);

        return redirect()->route('finance.index')->with('status', 'Payment recorded.');
    }
}

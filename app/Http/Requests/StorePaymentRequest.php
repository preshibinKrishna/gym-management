<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('billing.collect', app(TenantContext::class)->requireOrganization()) ?? false;
    }

    public function rules(): array
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return ['member_id' => ['nullable', 'integer', Rule::exists('members', 'id')->where('organization_id', $organization->id)], 'amount_paise' => ['required', 'integer', 'min:1'], 'method' => ['required', Rule::in(['cash', 'upi', 'bank_transfer', 'card'])], 'idempotency_key' => ['required', 'string', 'max:120'], 'invoice_id' => ['required', 'integer', Rule::exists('invoices', 'id')->where('organization_id', $organization->id)]];
    }
}

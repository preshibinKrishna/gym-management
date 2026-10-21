<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('billing.collect', app(TenantContext::class)->requireOrganization()) ?? false;
    }

    public function rules(): array
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return ['member_id' => ['nullable', 'integer', Rule::exists('members', 'id')->where('organization_id', $organization->id)], 'number' => ['required', 'string', 'max:60', Rule::unique('invoices')->where('organization_id', $organization->id)], 'due_on' => ['nullable', 'date'], 'discount_paise' => ['nullable', 'integer', 'min:0'], 'tax_paise' => ['nullable', 'integer', 'min:0'], 'description' => ['required', 'string', 'max:255'], 'quantity' => ['required', 'integer', 'min:1'], 'unit_price_paise' => ['required', 'integer', 'min:0']];
    }
}

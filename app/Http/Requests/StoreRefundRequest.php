<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('billing.refund', app(TenantContext::class)->requireOrganization()) ?? false;
    }

    public function rules(): array
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return ['payment_id' => ['required', 'integer', Rule::exists('payments', 'id')->where('organization_id', $organization->id)], 'amount_paise' => ['required', 'integer', 'min:1'], 'idempotency_key' => ['required', 'string', 'max:120'], 'reason' => ['required', 'string', 'max:500']];
    }
}

<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentIntentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('billing.collect', app(TenantContext::class)->requireOrganization()) ?? false;
    }

    public function rules(): array
    {
        return ['amount_paise' => ['required', 'integer', 'min:1'], 'reference' => ['required', 'string', 'max:100']];
    }
}

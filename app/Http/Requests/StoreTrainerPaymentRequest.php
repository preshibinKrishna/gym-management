<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTrainerPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return $this->user()?->hasPermission('trainers.manage', $organization) ?? false;
    }

    public function rules(): array
    {
        return [
            'amount_paise' => ['required', 'integer', 'min:1'],
            'payment_type' => ['required', Rule::in(['salary', 'daily_wage', 'bonus', 'advance', 'adjustment'])],
            'method' => ['required', Rule::in(['cash', 'upi', 'bank_transfer', 'card'])],
            'paid_on' => ['required', 'date'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

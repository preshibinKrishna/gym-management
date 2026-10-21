<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGymEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tenant = app(TenantContext::class);

        return $this->user()?->hasPermission('events.manage', $tenant->requireOrganization(), $tenant->branch()) ?? false;
    }

    public function rules(): array
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return ['branch_id' => ['required', 'integer', Rule::exists('branches', 'id')->where('organization_id', $organization->id)], 'name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:3000'], 'starts_at' => ['required', 'date'], 'ends_at' => ['required', 'date', 'after:starts_at'], 'capacity' => ['nullable', 'integer', 'min:1', 'max:10000'], 'price_paise' => ['nullable', 'integer', 'min:0']];
    }
}

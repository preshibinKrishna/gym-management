<?php

namespace App\Http\Requests;

use App\Models\Lead;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tenant = app(TenantContext::class);

        return $this->user()?->hasPermission('leads.manage', $tenant->requireOrganization(), $tenant->branch()) ?? false;
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::in(array_keys(Lead::STATUS_OPTIONS))]];
    }
}

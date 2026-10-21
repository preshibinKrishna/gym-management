<?php

namespace App\Http\Requests;

use App\Models\Lead;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tenant = app(TenantContext::class);

        return $this->user()?->hasPermission('leads.manage', $tenant->requireOrganization(), $tenant->branch()) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return [
            'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')->where('organization_id', $organization->id)],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'source' => ['nullable', 'string', 'max:80'],
            'follow_up_on' => ['nullable', 'date'],
            'trial_starts_at' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(array_keys(Lead::STATUS_OPTIONS))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

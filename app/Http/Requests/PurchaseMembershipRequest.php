<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tenant = app(TenantContext::class);

        return $this->user()?->hasPermission('memberships.manage', $tenant->requireOrganization(), $tenant->branch()) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return [
            'member_id' => ['required', 'integer', Rule::exists('members', 'id')->where('organization_id', $organization->id)],
            'plan_id' => ['required', 'integer', Rule::exists('plans', 'id')->where('organization_id', $organization->id)],
            'starts_on' => ['required', 'date'],
            'discount_paise' => ['nullable', 'integer', 'min:0'],
            'allowed_branch_ids' => ['required', 'array', 'min:1'],
            'allowed_branch_ids.*' => ['integer', Rule::exists('branches', 'id')->where('organization_id', $organization->id)],
        ];
    }
}

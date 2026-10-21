<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FreezeMembershipRequest extends FormRequest
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
            'membership_id' => ['required', 'integer', Rule::exists('memberships', 'id')->where('organization_id', $organization->id)],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }
}

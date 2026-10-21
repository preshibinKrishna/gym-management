<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('plans.manage', app(TenantContext::class)->requireOrganization()) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:40', 'alpha_dash', Rule::unique('plans')->where('organization_id', $organization->id)],
            'type' => ['required', Rule::in(['membership', 'session_pack', 'personal_training', 'class_addon', 'family'])],
            'duration_days' => ['nullable', 'integer', 'min:1', 'required_without:duration_months'],
            'duration_months' => ['nullable', 'integer', 'min:1', 'required_without:duration_days'],
            'session_limit' => ['nullable', 'integer', 'min:1'],
            'freeze_days_allowed' => ['required', 'integer', 'min:0', 'max:365'],
            'price_paise' => ['required', 'integer', 'min:0'],
            'joining_fee_paise' => ['nullable', 'integer', 'min:0'],
            'age_group' => ['required', Rule::in(['adult', 'child', 'all'])],
            'eligible_branch_ids' => ['nullable', 'array'],
            'eligible_branch_ids.*' => ['integer', Rule::exists('branches', 'id')->where('organization_id', $organization->id)],
        ];
    }
}

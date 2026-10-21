<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTrainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return $this->user()?->hasPermission('trainers.manage', $organization) ?? false;
    }

    public function rules(): array
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return [
            'home_branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')->where('organization_id', $organization->id)],
            'user_id' => ['nullable', 'integer', Rule::exists('organization_affiliations', 'user_id')->where('organization_id', $organization->id)->where('status', 'active')],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'specialization' => ['nullable', 'string', 'max:160'],
            'joined_on' => ['required', 'date'],
            'employment_type' => ['required', Rule::in(['salary', 'daily'])],
            'salary_paise' => ['nullable', 'integer', 'min:0'],
            'daily_wage_paise' => ['nullable', 'integer', 'min:0'],
            'address' => ['nullable', 'string', 'max:2000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}

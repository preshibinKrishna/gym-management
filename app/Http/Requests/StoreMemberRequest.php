<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tenant = app(TenantContext::class);

        return $this->user()?->hasPermission('members.create', $tenant->requireOrganization(), $tenant->branch()) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return [
            'home_branch_id' => ['required', 'integer', Rule::exists('branches', 'id')->where('organization_id', $organization->id)],
            'assigned_trainer_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'member_code' => ['required', 'string', 'max:40', Rule::unique('members')->where('organization_id', $organization->id)],
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['female', 'male', 'non_binary', 'prefer_not_to_say'])],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:40'],
            'joined_on' => ['required', 'date'],
            'goals' => ['nullable', 'array'],
            'goals.*' => ['string', 'max:80'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'guardian_relationship' => ['nullable', 'string', 'max:80'],
        ];
    }
}

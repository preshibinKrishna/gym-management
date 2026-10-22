<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMealPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('fitness.manage', app(TenantContext::class)->requireOrganization(), app(TenantContext::class)->branch()) ?? false;
    }

    public function rules(): array
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return ['member_ids' => ['required', 'array', 'min:1'], 'member_ids.*' => ['required', 'integer', 'distinct', Rule::exists('members', 'id')->where('organization_id', $organization->id)], 'name' => ['required', 'string', 'max:255'], 'starts_on' => ['required', 'date'], 'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'], 'meals' => ['required', 'string', 'max:5000'], 'preferences' => ['nullable', 'string', 'max:1000']];
    }
}

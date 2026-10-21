<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFitnessGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('fitness.manage', app(TenantContext::class)->requireOrganization(), app(TenantContext::class)->branch()) ?? false;
    }

    public function rules(): array
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return ['member_id' => ['required', 'integer', Rule::exists('members', 'id')->where('organization_id', $organization->id)], 'category' => ['required', 'string', 'max:80'], 'title' => ['required', 'string', 'max:255'], 'notes' => ['nullable', 'string', 'max:2000'], 'starts_on' => ['required', 'date'], 'target_on' => ['nullable', 'date', 'after_or_equal:starts_on'], 'review_on' => ['nullable', 'date', 'after_or_equal:starts_on']];
    }
}

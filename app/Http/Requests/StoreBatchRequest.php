<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tenant = app(TenantContext::class);

        return $this->user()?->hasPermission('batches.manage', $tenant->requireOrganization(), $tenant->branch()) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return [
            'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')->where('organization_id', $organization->id)],
            'trainer_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:40'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i'],
            'ends_next_day' => ['nullable', 'boolean'],
            'capacity' => ['required', 'integer', 'min:1', 'max:1000'],
            'eligibility' => ['required', Rule::in(['mixed', 'ladies_only', 'gents_only', 'kids'])],
            'weekdays' => ['nullable', 'array'],
            'weekdays.*' => ['integer', 'between:0,6'],
        ];
    }
}

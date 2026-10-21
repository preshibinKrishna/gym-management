<?php

namespace App\Http\Requests;

use App\Models\Branch;
use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SwitchTenantContextRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $organization = Organization::query()->whereKey($this->integer('organization_id'))->first();

        if ($user === null || $organization === null || ! $user->hasActiveAffiliation($organization)) {
            return false;
        }

        if (! $this->filled('branch_id')) {
            return true;
        }

        $branch = Branch::query()
            ->whereBelongsTo($organization)
            ->whereKey($this->integer('branch_id'))
            ->first();

        return $branch !== null && ($user->hasOrganizationWideRole($organization) || $user->isAssignedToBranch($branch));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'organization_id' => ['required', 'integer', Rule::exists('organizations', 'id')],
            'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')],
        ];
    }
}

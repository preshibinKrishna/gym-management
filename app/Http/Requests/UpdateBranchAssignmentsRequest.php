<?php

namespace App\Http\Requests;

use App\Models\Branch;
use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchAssignmentsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $branch = $this->route('branch');
        $organization = $branch instanceof Branch
            ? Organization::query()->find($branch->organization_id)
            : null;

        return $organization !== null
            && $this->user()?->hasPermission('roles.assign', $organization, $branch) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $branch = $this->route('branch');

        return [
            'user_ids' => ['sometimes', 'array'],
            'user_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('organization_affiliations', 'user_id')
                    ->where('organization_id', $branch->organization_id)
                    ->where('status', 'active'),
            ],
        ];
    }
}

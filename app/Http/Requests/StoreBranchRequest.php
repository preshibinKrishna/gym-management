<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return $this->user()?->hasPermission('branches.create', $organization) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:32', 'alpha_dash', Rule::unique('branches')->where('organization_id', $organization->id)],
            'timezone' => ['required', 'timezone:all'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['string', 'max:80'],
            'terms_summary' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

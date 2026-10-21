<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckInMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tenant = app(TenantContext::class);

        return $this->user()?->hasPermission('attendance.checkin', $tenant->requireOrganization(), $tenant->branch()) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return [
            'member_id' => ['required', 'integer', Rule::exists('members', 'id')->where('organization_id', $organization->id)],
            'batch_id' => ['nullable', 'integer', Rule::exists('batches', 'id')->where('organization_id', $organization->id)->where('branch_id', app(TenantContext::class)->requireBranch()->id)],
            'idempotency_key' => ['required', 'string', 'max:120'],
            'override_reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOperationalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tenant = app(TenantContext::class);
        $permission = $this->input('record_type') === 'camera' ? 'cctv.manage' : 'operations.manage';

        return $this->user()?->hasPermission($permission, $tenant->requireOrganization(), $tenant->branch()) ?? false;
    }

    public function rules(): array
    {
        $organization = app(TenantContext::class)->requireOrganization();
        $branch = ['nullable', 'integer', Rule::exists('branches', 'id')->where('organization_id', $organization->id)];

        return [
            'record_type' => ['required', Rule::in(['vendor', 'asset', 'maintenance', 'task', 'expense', 'camera'])],
            'branch_id' => [...$branch, 'required_if:record_type,asset,maintenance,camera'],
            'vendor_id' => ['nullable', 'integer', Rule::exists('vendors', 'id')->where('organization_id', $organization->id)],
            'asset_id' => ['nullable', 'integer', Rule::exists('assets', 'id')->where('organization_id', $organization->id)],
            'name' => ['nullable', 'string', 'max:255', 'required_if:record_type,vendor,asset,camera'], 'title' => ['nullable', 'string', 'max:255', 'required_if:record_type,maintenance,task'], 'description' => ['nullable', 'string', 'max:3000'],
            'category' => ['nullable', 'string', 'max:100', 'required_if:record_type,vendor,asset,expense'], 'phone' => ['nullable', 'string', 'max:40'], 'email' => ['nullable', 'email', 'max:255'],
            'asset_tag' => ['nullable', 'string', 'max:80', 'required_if:record_type,asset'], 'zone' => ['nullable', 'string', 'max:100'], 'condition' => ['nullable', 'string', 'max:50'], 'brand' => ['nullable', 'string', 'max:100'], 'model' => ['nullable', 'string', 'max:100'], 'serial_number' => ['nullable', 'string', 'max:120'], 'service_status' => ['nullable', Rule::in(['in_service', 'scheduled', 'out_of_service'])], 'purchased_on' => ['nullable', 'date'], 'warranty_until' => ['nullable', 'date', 'after_or_equal:purchased_on'],
            'amount_paise' => ['nullable', 'integer', 'min:0'], 'cost_paise' => ['nullable', 'integer', 'min:0'], 'bill_on' => ['nullable', 'date'], 'due_on' => ['nullable', 'date'],
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])], 'severity' => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'private_host' => ['nullable', 'string', 'max:255'], 'protocol' => ['nullable', Rule::in(['hls', 'webrtc', 'rtsp', 'unconfigured'])], 'nvr_channel' => ['nullable', 'string', 'max:40'],
        ];
    }
}

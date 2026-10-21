<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tenant = app(TenantContext::class);

        return $this->user()?->hasPermission('notifications.send', $tenant->requireOrganization(), $tenant->branch()) ?? false;
    }

    public function rules(): array
    {
        $organization = app(TenantContext::class)->requireOrganization();

        return ['member_id' => ['required', 'integer', Rule::exists('members', 'id')->where('organization_id', $organization->id)], 'channel' => ['required', Rule::in(['email', 'sms', 'whatsapp'])], 'template' => ['required', 'string', 'max:100'], 'idempotency_key' => ['required', 'string', 'max:120'], 'payload' => ['nullable', 'array']];
    }
}

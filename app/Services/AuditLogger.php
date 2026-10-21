<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        string $event,
        ?User $actor,
        ?Organization $organization = null,
        ?Branch $branch = null,
        ?Model $auditable = null,
        array $metadata = [],
        ?string $reason = null,
        ?Request $request = null,
    ): AuditLog {
        return AuditLog::create([
            'organization_id' => $organization?->id,
            'branch_id' => $branch?->id,
            'actor_user_id' => $actor?->id,
            'event' => $event,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'reason' => $reason,
            'metadata' => $metadata,
        ]);
    }
}

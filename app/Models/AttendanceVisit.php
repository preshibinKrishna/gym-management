<?php

namespace App\Models;

use Database\Factories\AttendanceVisitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'branch_id', 'member_id', 'membership_id', 'batch_id', 'operator_user_id', 'checked_in_at', 'checked_out_at', 'source', 'device_identifier', 'idempotency_key', 'session_consumed', 'override_reason', 'denial_reason'])]
class AttendanceVisit extends Model
{
    /** @use HasFactory<AttendanceVisitFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'session_consumed' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }
}

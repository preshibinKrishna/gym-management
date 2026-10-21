<?php

namespace App\Models;

use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'branch_id', 'name', 'phone', 'email', 'source', 'follow_up_on', 'trial_starts_at', 'status', 'notes', 'converted_member_id', 'assigned_to'])]
class Lead extends Model
{
    public const STATUS_OPTIONS = [
        'not_contacted' => 'Not contacted',
        'contacted' => 'Contacted',
        'follow_up' => 'Follow-up',
        'converted' => 'Converted',
        'closed' => 'Closed',
    ];

    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'follow_up_on' => 'date',
            'trial_starts_at' => 'datetime',
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

    public function convertedMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'converted_member_id');
    }
}

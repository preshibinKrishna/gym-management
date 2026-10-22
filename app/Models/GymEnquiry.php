<?php

namespace App\Models;

use Database\Factories\GymEnquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['gym_name', 'owner_name', 'email', 'phone', 'city', 'source', 'follow_up_on', 'status', 'notes'])]
class GymEnquiry extends Model
{
    public const STATUS_OPTIONS = [
        'new' => 'New enquiry',
        'contacted' => 'Contacted',
        'follow_up' => 'Follow-up',
        'demo_scheduled' => 'Demo scheduled',
        'signed_up' => 'Signed up',
        'closed' => 'Closed',
    ];

    /** @use HasFactory<GymEnquiryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'follow_up_on' => 'date',
        ];
    }
}

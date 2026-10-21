<?php

namespace App\Models;

use Database\Factories\OperationalTaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'branch_id', 'assignee_user_id', 'title', 'description', 'priority', 'status', 'due_on', 'checklist'])]
class OperationalTask extends Model
{
    /** @use HasFactory<OperationalTaskFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['due_on' => 'date', 'checklist' => 'array'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_user_id');
    }
}

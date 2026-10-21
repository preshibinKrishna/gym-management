<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'home_branch_id', 'user_id', 'name', 'email', 'phone', 'specialization', 'joined_on', 'employment_type', 'salary_paise', 'daily_wage_paise', 'avatar_path', 'status', 'address', 'emergency_contact_name', 'emergency_contact_phone', 'notes'])]
class Trainer extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['joined_on' => 'date', 'salary_paise' => 'integer', 'daily_wage_paise' => 'integer'];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function homeBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'home_branch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TrainerPayment::class);
    }

    public function compensationLabel(): string
    {
        return $this->employment_type === 'daily'
            ? '₹'.number_format(($this->daily_wage_paise ?? 0) / 100, 2).' / day'
            : '₹'.number_format(($this->salary_paise ?? 0) / 100, 2).' / month';
    }
}

<?php

namespace App\Models;

use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'code', 'legal_name', 'status', 'currency', 'default_timezone', 'settings'])]
class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function affiliations(): HasMany
    {
        return $this->hasMany(OrganizationAffiliation::class);
    }

    public function roleAssignments(): HasMany
    {
        return $this->hasMany(RoleAssignment::class);
    }

    public function tenantSettings(): HasMany
    {
        return $this->hasMany(TenantSetting::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function trainers(): HasMany
    {
        return $this->hasMany(Trainer::class);
    }
}

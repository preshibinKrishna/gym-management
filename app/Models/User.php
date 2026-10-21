<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'avatar_path', 'bio', 'timezone', 'password', 'is_platform_super_admin', 'status', 'two_factor_confirmed_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_platform_super_admin' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function affiliations(): HasMany
    {
        return $this->hasMany(OrganizationAffiliation::class);
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_affiliations')
            ->withPivot(['status', 'verified_at'])
            ->withTimestamps();
    }

    public function roleAssignments(): HasMany
    {
        return $this->hasMany(RoleAssignment::class);
    }

    public function branchAssignments(): HasMany
    {
        return $this->hasMany(BranchAssignment::class);
    }

    public function trainerProfiles(): HasMany
    {
        return $this->hasMany(Trainer::class);
    }

    public function hasActiveAffiliation(Organization $organization): bool
    {
        return $this->affiliations()
            ->whereBelongsTo($organization)
            ->where('status', 'active')
            ->exists();
    }

    public function isAssignedToBranch(Branch $branch): bool
    {
        return $this->branchAssignments()
            ->whereBelongsTo($branch)
            ->where('organization_id', $branch->organization_id)
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->exists();
    }

    public function hasPermission(string $permissionKey, ?Organization $organization = null, ?Branch $branch = null): bool
    {
        if ($this->is_platform_super_admin) {
            return true;
        }

        if ($organization === null || ! $this->hasActiveAffiliation($organization)) {
            return false;
        }

        if ($branch !== null && $branch->organization_id !== $organization->id) {
            return false;
        }

        $hasPermission = $this->roleAssignments()
            ->where('organization_id', $organization->id)
            ->where(function ($query) use ($branch): void {
                $query->whereNull('branch_id');

                if ($branch !== null) {
                    $query->orWhere('branch_id', $branch->id);
                }
            })
            ->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->whereHas('role.permissions', fn ($query) => $query->where('key', $permissionKey))
            ->exists();

        if (! $hasPermission) {
            return false;
        }

        return $branch === null || $this->hasOrganizationWideRole($organization) || $this->isAssignedToBranch($branch);
    }

    public function hasOrganizationWideRole(Organization $organization): bool
    {
        return $this->roleAssignments()
            ->where('organization_id', $organization->id)
            ->whereNull('branch_id')
            ->exists();
    }
}

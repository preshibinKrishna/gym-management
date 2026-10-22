<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

class BranchPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        $tenant = app(TenantContext::class);

        return $user->hasPermission('branches.view', $tenant->organization(), $tenant->branch());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Branch $branch): bool
    {
        $organization = app(TenantContext::class)->organization();

        return $organization !== null
            && $branch->organization_id === $organization->id
            && $user->hasPermission('branches.view', $organization, $branch);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('branches.create', app(TenantContext::class)->organization());
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Branch $branch): bool
    {
        $organization = app(TenantContext::class)->organization();

        return $organization !== null
            && $branch->organization_id === $organization->id
            && $user->hasPermission('branches.update', $organization, $branch);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Branch $branch): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Branch $branch): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Branch $branch): bool
    {
        return false;
    }

    public function assign(User $user, Branch $branch): bool
    {
        $organization = app(TenantContext::class)->organization();

        return $organization !== null
            && $branch->organization_id === $organization->id
            && $user->hasPermission('roles.assign', $organization, $branch);
    }
}

<?php

namespace App\Policies;

use App\Models\Plan;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

class PlanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('plans.view', app(TenantContext::class)->organization());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Plan $plan): bool
    {
        $organization = app(TenantContext::class)->organization();

        return $organization !== null
            && $plan->organization_id === $organization->id
            && $user->hasPermission('plans.view', $organization);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('plans.manage', app(TenantContext::class)->organization());
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Plan $plan): bool
    {
        $organization = app(TenantContext::class)->organization();

        return $organization !== null
            && $plan->organization_id === $organization->id
            && $user->hasPermission('plans.manage', $organization);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Plan $plan): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Plan $plan): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Plan $plan): bool
    {
        return false;
    }
}

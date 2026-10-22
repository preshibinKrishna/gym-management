<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

class MemberPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        $tenant = app(TenantContext::class);

        return $user->hasPermission('members.view', $tenant->organization(), $tenant->branch());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Member $member): bool
    {
        $organization = app(TenantContext::class)->organization();

        if ($organization === null || $member->organization_id !== $organization->id) {
            return false;
        }

        return $user->hasPermission('members.view', $organization, $member->homeBranch)
            || $member->user_id === $user->id
            || ($member->assigned_trainer_user_id === $user->id && $user->isAssignedToBranch($member->homeBranch))
            || $member->guardianRelationships()
                ->where('guardian_user_id', $user->id)
                ->where('can_view_profile', true)
                ->whereNull('revoked_at')
                ->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('members.create', app(TenantContext::class)->organization());
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Member $member): bool
    {
        $organization = app(TenantContext::class)->organization();

        return $organization !== null
            && $member->organization_id === $organization->id
            && $user->hasPermission('members.update', $organization, $member->homeBranch);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Member $member): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Member $member): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Member $member): bool
    {
        return false;
    }
}

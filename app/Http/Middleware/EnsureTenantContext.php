<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\Organization;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $organizationId = $request->session()->get('tenant.organization_id');
        $branchId = $request->session()->get('tenant.branch_id');

        if ($organizationId === null) {
            abort_unless($user->is_platform_super_admin, 403, 'Tenant context is required.');

            app(TenantContext::class)->set(null, null);

            return $next($request);
        }

        $organization = Organization::query()->whereKey($organizationId)->first();

        abort_unless($organization && $user->hasActiveAffiliation($organization), 403, 'Invalid tenant context.');

        $branch = null;

        if ($branchId !== null) {
            $branch = Branch::query()
                ->whereBelongsTo($organization)
                ->whereKey($branchId)
                ->first();

            abort_unless($branch, 403, 'Invalid branch context.');
            abort_unless($user->hasOrganizationWideRole($organization) || $user->isAssignedToBranch($branch), 403, 'Branch assignment is required.');
        }

        app(TenantContext::class)->set($organization, $branch);

        return $next($request);
    }
}

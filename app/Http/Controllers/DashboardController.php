<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Payment;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(TenantContext $tenantContext): View|RedirectResponse
    {
        $organization = $tenantContext->organization();
        $branch = $tenantContext->branch();

        if ($organization !== null
            && request()->user()->memberProfiles()->where('organization_id', $organization->id)->exists()
            && ! request()->user()->roleAssignments()->where('organization_id', $organization->id)->exists()) {
            return redirect()->route('member-portal');
        }
        $availableBranches = $organization
            ? Branch::query()->whereBelongsTo($organization)
                ->when(! request()->user()->hasOrganizationWideRole($organization), fn ($query) => $query->whereHas('assignments', fn ($assignments) => $assignments
                    ->where('user_id', request()->user()->id)
                    ->where('status', 'active')
                    ->where(fn ($assignment) => $assignment->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                    ->where(fn ($assignment) => $assignment->whereNull('ends_at')->orWhere('ends_at', '>=', now()))))
                ->orderBy('name')->get()
            : collect();

        $recentAuditLogs = AuditLog::query()
            ->when($organization, fn ($query) => $query->whereBelongsTo($organization))
            ->when($branch, fn ($query) => $query->whereBelongsTo($branch))
            ->with(['actor', 'branch'])
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard', [
            'organization' => $organization,
            'branch' => $branch,
            'branchCount' => $organization && request()->user()->hasOrganizationWideRole($organization) ? $organization->branches()->count() : $availableBranches->count(),
            'assignedStaffCount' => $organization
                ? ($branch
                    ? $branch->assignments()->where('status', 'active')->distinct('user_id')->count('user_id')
                    : $organization->roleAssignments()->distinct('user_id')->count('user_id'))
                : 0,
            'recentAuditLogs' => $recentAuditLogs,
            'availableBranches' => $availableBranches,
            'activeMemberCount' => $organization
                ? $organization->members()->where('status', 'active')->when($branch, fn ($query) => $query->where('home_branch_id', $branch->id))->count()
                : 0,
            'monthCollectionsPaise' => $organization
                ? Payment::whereBelongsTo($organization)
                    ->whereBetween('received_on', [now()->startOfMonth()->toDateString(), now()->toDateString()])
                    ->when($branch, fn ($query) => $query->whereHas('member', fn ($members) => $members->where('home_branch_id', $branch->id)))
                    ->sum('amount_paise')
                : 0,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Payment;
use App\Support\Tenancy\TenantContext;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(TenantContext $tenantContext): View
    {
        $organization = $tenantContext->organization();
        $branch = $tenantContext->branch();

        $recentAuditLogs = AuditLog::query()
            ->when($organization, fn ($query) => $query->whereBelongsTo($organization))
            ->with(['actor', 'branch'])
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard', [
            'organization' => $organization,
            'branch' => $branch,
            'branchCount' => $organization?->branches()->count() ?? 0,
            'assignedStaffCount' => $organization?->roleAssignments()->distinct('user_id')->count('user_id') ?? 0,
            'recentAuditLogs' => $recentAuditLogs,
            'availableBranches' => $organization
                ? Branch::query()->whereBelongsTo($organization)->orderBy('name')->get()
                : collect(),
            'activeMemberCount' => $organization?->members()->where('status', 'active')->count() ?? 0,
            'monthCollectionsPaise' => $organization ? Payment::whereBelongsTo($organization)->whereBetween('received_on', [now()->startOfMonth()->toDateString(), now()->toDateString()])->sum('amount_paise') : 0,
        ]);
    }
}

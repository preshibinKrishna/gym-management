<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchAssignmentsRequest;
use App\Models\Branch;
use App\Models\BranchAssignment;
use App\Models\Organization;
use App\Models\RoleAssignment;
use App\Services\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(TenantContext $tenantContext): View
    {
        Gate::authorize('viewAny', Branch::class);

        $organization = $tenantContext->requireOrganization();
        $visibleBranches = Branch::query()
            ->whereBelongsTo($organization)
            ->when(! request()->user()->hasOrganizationWideRole($organization), fn ($query) => $query->whereHas('assignments', fn ($assignments) => $assignments
                ->where('user_id', request()->user()->id)
                ->where('status', 'active')
                ->where(fn ($assignment) => $assignment->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($assignment) => $assignment->whereNull('ends_at')->orWhere('ends_at', '>=', now()))));
        $branches = (clone $visibleBranches)
            ->with('manager')
            ->orderBy('name')
            ->paginate(15);

        return view('branches.index', [
            'organization' => $organization,
            'branches' => $branches,
            'activeBranchCount' => (clone $visibleBranches)->where('status', 'active')->count(),
            'totalCapacity' => (int) (clone $visibleBranches)->sum('capacity'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(TenantContext $tenantContext): View
    {
        Gate::authorize('create', Branch::class);

        return view('branches.create', [
            'organization' => $tenantContext->requireOrganization(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBranchRequest $request, TenantContext $tenantContext, AuditLogger $auditLogger): RedirectResponse
    {
        $organization = $tenantContext->requireOrganization();
        $validated = $request->validated();

        $branch = Branch::create([
            ...$validated,
            'organization_id' => $organization->id,
            'status' => 'active',
        ]);

        $auditLogger->record('branch.created', $request->user(), $organization, $branch, $branch, [
            'code' => $branch->code,
        ], request: $request);

        return redirect()->route('branches.show', $branch)->with('status', 'Branch created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch, TenantContext $tenantContext): View
    {
        $organization = $tenantContext->requireOrganization();

        abort_unless($branch->organization_id === $organization->id, 404);
        Gate::authorize('view', $branch);

        $branch->load(['manager', 'operatingIntervals', 'closures']);
        $staff = $organization->affiliations()
            ->where('status', 'active')
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->sortBy('name');

        return view('branches.show', [
            'organization' => $organization,
            'branch' => $branch,
            'staff' => $staff,
            'assignedStaffIds' => $branch->assignments()->where('status', 'active')->pluck('user_id')->all(),
        ]);
    }

    public function updateAssignments(UpdateBranchAssignmentsRequest $request, Branch $branch): RedirectResponse
    {
        $organization = Organization::query()->findOrFail($branch->organization_id);
        $userIds = $request->validated('user_ids', []);

        BranchAssignment::query()
            ->where('branch_id', $branch->id)
            ->where('organization_id', $organization->id)
            ->whereNotIn('user_id', $userIds)
            ->update(['status' => 'inactive']);

        foreach ($userIds as $userId) {
            BranchAssignment::query()->updateOrCreate(
                ['branch_id' => $branch->id, 'user_id' => $userId],
                ['organization_id' => $organization->id, 'status' => 'active', 'starts_at' => now(), 'ends_at' => null],
            );

            $scopedRoleIds = RoleAssignment::query()
                ->where('organization_id', $organization->id)
                ->where('user_id', $userId)
                ->whereHas('role', fn ($query) => $query->where('scope', 'branch'))
                ->distinct()
                ->pluck('role_id');

            foreach ($scopedRoleIds as $roleId) {
                RoleAssignment::query()->updateOrCreate(
                    ['organization_id' => $organization->id, 'branch_id' => $branch->id, 'user_id' => $userId, 'role_id' => $roleId],
                    ['assigned_by' => $request->user()->id, 'starts_at' => now(), 'ends_at' => null],
                );
            }
        }

        return back()->with('status', 'Staff branch access updated.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {
        abort(501);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreBranchRequest $request, Branch $branch)
    {
        abort(501);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        abort(501);
    }
}

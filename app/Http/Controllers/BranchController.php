<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchRequest;
use App\Models\Branch;
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
        $branches = Branch::query()
            ->whereBelongsTo($organization)
            ->with('manager')
            ->orderBy('name')
            ->paginate(15);

        return view('branches.index', [
            'organization' => $organization,
            'branches' => $branches,
            'activeBranchCount' => $organization->branches()->where('status', 'active')->count(),
            'totalCapacity' => (int) $organization->branches()->sum('capacity'),
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

        return view('branches.show', [
            'organization' => $organization,
            'branch' => $branch,
        ]);
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

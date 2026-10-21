<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlanRequest;
use App\Models\Plan;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(TenantContext $tenantContext): View
    {
        Gate::authorize('viewAny', Plan::class);

        return view('plans.index', [
            'plans' => Plan::query()
                ->whereBelongsTo($tenantContext->requireOrganization())
                ->orderBy('name')
                ->paginate(15),
            'activePlanCount' => $tenantContext->requireOrganization()->plans()->where('is_active', true)->count(),
        ]);
    }

    public function create(TenantContext $tenantContext): View
    {
        Gate::authorize('create', Plan::class);

        return view('plans.create', [
            'branches' => $tenantContext->requireOrganization()->branches()->orderBy('name')->get(),
        ]);
    }

    public function store(StorePlanRequest $request, TenantContext $tenantContext): RedirectResponse
    {
        $plan = Plan::create([
            ...$request->validated(),
            'organization_id' => $tenantContext->requireOrganization()->id,
            'joining_fee_paise' => $request->integer('joining_fee_paise'),
            'eligible_branch_ids' => $request->input('eligible_branch_ids'),
            'features' => ['Created from Phase 2 catalog UI'],
            'is_active' => true,
        ]);

        return redirect()->route('plans.show', $plan)->with('status', 'Plan created.');
    }

    public function show(Plan $plan, TenantContext $tenantContext): View
    {
        abort_unless($plan->organization_id === $tenantContext->requireOrganization()->id, 404);
        Gate::authorize('view', $plan);

        return view('plans.show', ['plan' => $plan]);
    }
}

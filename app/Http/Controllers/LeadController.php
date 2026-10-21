<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadStatusRequest;
use App\Models\Lead;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(TenantContext $tenantContext): View
    {
        $organization = $tenantContext->requireOrganization();

        abort_unless(request()->user()?->hasPermission('leads.manage', $organization, $tenantContext->branch()), 403);

        $statusFilter = request()->validate(['status' => ['nullable', Rule::in(array_keys(Lead::STATUS_OPTIONS))]])['status'] ?? null;
        $statusCounts = Lead::query()->whereBelongsTo($organization)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status')->all();
        $statusCounts['not_contacted'] = ($statusCounts['not_contacted'] ?? 0) + ($statusCounts['new'] ?? 0);
        unset($statusCounts['new']);

        $leads = Lead::query()->whereBelongsTo($organization)
            ->when($statusFilter, fn ($query) => $query->whereIn('status', $statusFilter === 'not_contacted' ? ['not_contacted', 'new'] : [$statusFilter]))
            ->latest()->paginate(15)->withQueryString();

        return view('leads.index', ['leads' => $leads, 'statusCounts' => $statusCounts, 'statusFilter' => $statusFilter, 'statusOptions' => Lead::STATUS_OPTIONS]);
    }

    public function create(): View
    {
        return view('leads.create');
    }

    public function store(StoreLeadRequest $request, TenantContext $tenantContext): RedirectResponse
    {
        $organization = $tenantContext->requireOrganization();
        $lead = Lead::create([
            ...$request->validated(),
            'organization_id' => $organization->id,
            'branch_id' => $request->integer('branch_id') ?: $tenantContext->branchId(),
            'status' => $request->input('status', 'not_contacted'),
        ]);

        return redirect()->route('leads.show', $lead)->with('status', 'Lead created.');
    }

    public function updateStatus(UpdateLeadStatusRequest $request, Lead $lead, TenantContext $tenantContext): RedirectResponse
    {
        abort_unless($lead->organization_id === $tenantContext->requireOrganization()->id, 404);
        $lead->update(['status' => $request->validated('status')]);

        return back()->with('status', 'Lead status updated.');
    }

    public function show(Lead $lead, TenantContext $tenantContext): View
    {
        $organization = $tenantContext->requireOrganization();

        abort_unless($lead->organization_id === $organization->id, 404);
        abort_unless(request()->user()?->hasPermission('leads.manage', $organization, $lead->branch), 403);

        return view('leads.show', ['lead' => $lead]);
    }
}
